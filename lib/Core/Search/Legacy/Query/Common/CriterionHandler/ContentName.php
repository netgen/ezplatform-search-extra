<?php

declare(strict_types=1);

namespace Netgen\EzPlatformSearchExtra\Core\Search\Legacy\Query\Common\CriterionHandler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\ContentName as ContentNameCriterion;
use RuntimeException;

/**
 * @see \Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\ContentName
 */
final class ContentName extends CriterionHandler
{
    protected $languageHandler;

    public function __construct(Connection $connection, LanguageHandler $languageHandler)
    {
        parent::__construct($connection);

        $this->languageHandler = $languageHandler;
    }

    public function accept(Criterion $criterion): bool
    {
        return $criterion instanceof ContentNameCriterion;
    }

    /**
     * @param \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter $converter
     * @param \Doctrine\DBAL\Query\QueryBuilder $queryBuilder
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param array $languageSettings
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @return string
     */
    public function handle(
        CriteriaConverter $converter,
        QueryBuilder $queryBuilder,
        Criterion $criterion,
        array $languageSettings
    ): string {
        $subQueryBuilder = $this->connection->createQueryBuilder();

        $subQueryBuilder
            ->select('contentobject_id')
            ->from('ezcontentobject_name')
            ->where(
                $subQueryBuilder->expr()->and(
                    $this->getCriterionCondition($queryBuilder, $subQueryBuilder, $criterion),
                    $this->getLanguageCondition($queryBuilder, $subQueryBuilder, $languageSettings)
                )
            );

        return $queryBuilder->expr()->in(
            'c.id',
            $subQueryBuilder->getSQL()
        );
    }

    private function getCriterionCondition(
        QueryBuilder $queryBuilder,
        QueryBuilder $subQueryBuilder,
        Criterion $criterion
    ): string {
        $column = 'ezcontentobject_name.name';

        switch ($criterion->operator) {
            case Criterion\Operator::EQ:
            case Criterion\Operator::IN:
                return $subQueryBuilder->expr()->in(
                    $column,
                    $queryBuilder->createNamedParameter($criterion->value, Connection::PARAM_STR_ARRAY)
                );

            case Criterion\Operator::GT:
            case Criterion\Operator::GTE:
            case Criterion\Operator::LT:
            case Criterion\Operator::LTE:
                $operatorFunction = $this->comparatorMap[$criterion->operator];

                return $subQueryBuilder->expr()->$operatorFunction(
                    $column,
                    $queryBuilder->createNamedParameter(reset($criterion->value), ParameterType::STRING)
                );

            case Criterion\Operator::BETWEEN:
                return $this->dbPlatform->getBetweenExpression(
                    $column,
                    $queryBuilder->createNamedParameter($criterion->value[0], ParameterType::STRING),
                    $queryBuilder->createNamedParameter($criterion->value[1], ParameterType::STRING)
                );

            case Operator::LIKE:
                $string = $this->prepareLikeString(reset($criterion->value));
                return $subQueryBuilder->expr()->like(
                    $column,
                    $queryBuilder->createNamedParameter($string, ParameterType::STRING)
                );

            default:
                throw new RuntimeException(
                    "Unknown operator '{$criterion->operator}' for ContentId criterion handler."
                );
        }
    }

    /**
     * @param \Doctrine\DBAL\Query\QueryBuilder $queryBuilder
     * @param \Doctrine\DBAL\Query\QueryBuilder $subQueryBuilder
     * @param array $languageSettings
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @return \Doctrine\DBAL\Query\Expression\CompositeExpression|string
     */
    protected function getLanguageCondition(
        QueryBuilder $queryBuilder,
        QueryBuilder $subQueryBuilder,
        array $languageSettings
    ) {
        // 1. Use main language(s) by default
        if (empty($languageSettings['languages'])) {
            return $subQueryBuilder->expr()->gt(
                $this->dbPlatform->getBitAndComparisonExpression(
                    'c.initial_language_id',
                    'ezcontentobject_name.language_id'
                ),
                $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)
            );
        }

        // 2. Otherwise use prioritized languages
        $leftSide = $this->dbPlatform->getBitAndComparisonExpression(
            sprintf(
                'c.language_mask - %s',
                $this->dbPlatform->getBitAndComparisonExpression(
                    'c.language_mask',
                    'ezcontentobject_name.language_id'
                )
            ),
            $queryBuilder->createNamedParameter(1, ParameterType::INTEGER)
        );
        $rightSide = $this->dbPlatform->getBitAndComparisonExpression(
            'ezcontentobject_name.language_id',
            $queryBuilder->createNamedParameter(1, ParameterType::INTEGER)
        );

        for (
            $index = count($languageSettings['languages']) - 1, $multiplier = 2;
            $index >= 0;
            $index--, $multiplier *= 2
        ) {
            $languageCode = $languageSettings['languages'][$index];
            $languageId = $this->languageHandler->loadByLanguageCode($languageCode)->id;

            $addToLeftSide = $this->dbPlatform->getBitAndComparisonExpression(
                sprintf(
                    'c.language_mask - %s',
                    $this->dbPlatform->getBitAndComparisonExpression(
                        'c.language_mask',
                        'ezcontentobject_name.language_id'
                    )
                ),
                $queryBuilder->createNamedParameter($languageId, ParameterType::INTEGER)
            );
            $addToRightSide = $this->dbPlatform->getBitAndComparisonExpression(
                'ezcontentobject_name.language_id',
                $queryBuilder->createNamedParameter($languageId, ParameterType::INTEGER)
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

            $leftSide = "$leftSide + ($addToLeftSide)";
            $rightSide = "$rightSide + ($addToRightSide)";
        }

        return $subQueryBuilder->expr()->and(
            $subQueryBuilder->expr()->gt(
                $this->dbPlatform->getBitAndComparisonExpression(
                    'c.language_mask',
                    'ezcontentobject_name.language_id'
                ),
                $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)
            ),
            $subQueryBuilder->expr()->lt($leftSide, $rightSide)
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
