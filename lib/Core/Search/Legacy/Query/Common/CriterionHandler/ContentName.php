<?php

declare(strict_types=1);

namespace Netgen\EzPlatformSearchExtra\Core\Search\Legacy\Query\Common\CriterionHandler;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\ContentName as ContentNameCriterion;
use PDO;
use RuntimeException;

/**
 * @see \Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\ContentName
 */
final class ContentName extends CriterionHandler
{
    protected $languageHandler;

    public function __construct(DatabaseHandler $dbHandler, LanguageHandler $languageHandler)
    {
        parent::__construct($dbHandler);

        $this->languageHandler = $languageHandler;
    }

    public function accept(Criterion $criterion): bool
    {
        return $criterion instanceof ContentNameCriterion;
    }

    /**
     * @param \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter $converter
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param array $languageSettings
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @return string
     */
    public function handle(
        CriteriaConverter $converter,
        SelectQuery $query,
        Criterion $criterion,
        array $languageSettings
    ): string {
        $subSelect = $query->subSelect();
        $subSelect->select($this->dbHandler->quoteColumn('contentobject_id'));
        $subSelect->from($this->dbHandler->quoteTable('ezcontentobject_name'));
        $subSelect->where(
            $subSelect->expr->lAnd(
                $this->getCriterionCondition($query, $criterion),
                $this->getLanguageCondition($subSelect, $languageSettings)
            )
        );

        return $query->expr->in(
            $this->dbHandler->quoteColumn('id', 'ezcontentobject'),
            $subSelect
        );
    }

    private function getCriterionCondition(SelectQuery $query, Criterion $criterion): string
    {
        $column = $this->dbHandler->quoteColumn('name', 'ezcontentobject_name');

        switch ($criterion->operator) {
            case Criterion\Operator::EQ:
            case Criterion\Operator::IN:
                return $query->expr->in($column, $criterion->value);

            case Criterion\Operator::GT:
            case Criterion\Operator::GTE:
            case Criterion\Operator::LT:
            case Criterion\Operator::LTE:
                $operatorFunction = $this->comparatorMap[$criterion->operator];

                return $query->expr->$operatorFunction(
                    $column,
                    $query->bindValue(reset($criterion->value))
                );

            case Criterion\Operator::BETWEEN:
                return $query->expr->between(
                    $column,
                    $query->bindValue($criterion->value[0]),
                    $query->bindValue($criterion->value[1])
                );

            case Operator::LIKE:
                $string = $this->prepareLikeString(reset($criterion->value));
                return $query->expr->like(
                    $column,
                    $query->bindValue($string)
                );

            default:
                throw new RuntimeException(
                    "Unknown operator '{$criterion->operator}' for ContentId criterion handler."
                );
        }
    }

    /**
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param array $languageSettings
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @return string
     */
    protected function getLanguageCondition(SelectQuery $query, array $languageSettings): string
    {
        // 1. Use main language(s) by default
        if (empty($languageSettings['languages'])) {
            return $query->expr->gt(
                $query->expr->bitAnd(
                    $this->dbHandler->quoteColumn('initial_language_id', 'ezcontentobject'),
                    $this->dbHandler->quoteColumn('language_id', 'ezcontentobject_name')
                ),
                $query->bindValue(0, null, PDO::PARAM_INT)
            );
        }

        // 2. Otherwise use prioritized languages
        $leftSide = $query->expr->bitAnd(
            $query->expr->sub(
                $this->dbHandler->quoteColumn('language_mask', 'ezcontentobject'),
                $query->expr->bitAnd(
                    $this->dbHandler->quoteColumn('language_mask', 'ezcontentobject'),
                    $this->dbHandler->quoteColumn('language_id', 'ezcontentobject_name')
                )
            ),
            $query->bindValue(1, null, PDO::PARAM_INT)
        );
        $rightSide = $query->expr->bitAnd(
            $this->dbHandler->quoteColumn('language_id', 'ezcontentobject_name'),
            $query->bindValue(1, null, PDO::PARAM_INT)
        );

        for (
            $index = count($languageSettings['languages']) - 1, $multiplier = 2;
            $index >= 0;
            $index--, $multiplier *= 2
        ) {
            $languageCode = $languageSettings['languages'][$index];
            $languageId = $this->languageHandler->loadByLanguageCode($languageCode)->id;

            $addToLeftSide = $query->expr->bitAnd(
                $query->expr->sub(
                    $this->dbHandler->quoteColumn('language_mask', 'ezcontentobject'),
                    $query->expr->bitAnd(
                        $this->dbHandler->quoteColumn('language_mask', 'ezcontentobject'),
                        $this->dbHandler->quoteColumn('language_id', 'ezcontentobject_name')
                    )
                ),
                $query->bindValue($languageId, null, PDO::PARAM_INT)
            );
            $addToRightSide = $query->expr->bitAnd(
                $this->dbHandler->quoteColumn('language_id', 'ezcontentobject_name'),
                $query->bindValue($languageId, null, PDO::PARAM_INT)
            );

            if ($multiplier > $languageId) {
                $factor = $multiplier / $languageId;
                /** @noinspection PhpStatementHasEmptyBodyInspection */
                /** @noinspection MissingOrEmptyGroupStatementInspection */
                /** @noinspection LoopWhichDoesNotLoopInspection */
                for ($shift = 0; $factor > 1; $factor /= 2, $shift++) {}
                $factorTerm = ' << ' . $shift;
                $addToLeftSide .= $factorTerm;
                $addToRightSide .= $factorTerm;
            } elseif ($multiplier < $languageId) {
                $factor = $languageId / $multiplier;
                /** @noinspection PhpStatementHasEmptyBodyInspection */
                /** @noinspection MissingOrEmptyGroupStatementInspection */
                /** @noinspection LoopWhichDoesNotLoopInspection */
                for ($shift = 0; $factor > 1; $factor /= 2, $shift++) {}
                $factorTerm = ' >> ' . $shift;
                $addToLeftSide .= $factorTerm;
                $addToRightSide .= $factorTerm;
            }

            $leftSide = $query->expr->add($leftSide, "($addToLeftSide)");
            $rightSide = $query->expr->add($rightSide, "($addToRightSide)");
        }

        return $query->expr->lAnd(
            $query->expr->gt(
                $query->expr->bitAnd(
                    $this->dbHandler->quoteColumn('language_mask', 'ezcontentobject'),
                    $this->dbHandler->quoteColumn('language_id', 'ezcontentobject_name')
                ),
                $query->bindValue(0, null, PDO::PARAM_INT)
            ),
            $query->expr->lt($leftSide, $rightSide)
        );
    }

    /**
     * Returns the given $string prepared for use in SQL LIKE clause.
     *
     * LIKE clause wildcards '%' and '_' contained in the given $string will be escaped.
     *
     * @param $string
     *
     * @return string
     */
    protected function prepareLikeString($string): string
    {
        $string = addcslashes($string, '%_');

        return str_replace('*', '%', $string);
    }
}
