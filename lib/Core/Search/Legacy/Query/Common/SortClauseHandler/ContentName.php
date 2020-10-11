<?php

declare(strict_types=1);

namespace Netgen\EzPlatformSearchExtra\Core\Search\Legacy\Query\Common\SortClauseHandler;

use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\SortClause\ContentName as ContentNameSortClause;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;
use PDO;

class ContentName extends SortClauseHandler
{
    protected $languageHandler;

    public function __construct(DatabaseHandler $dbHandler, LanguageHandler $languageHandler)
    {
        parent::__construct($dbHandler);

        $this->languageHandler = $languageHandler;
    }

    public function accept(SortClause $sortClause): bool
    {
        return $sortClause instanceof ContentNameSortClause;
    }

    public function applySelect(SelectQuery $query, SortClause $sortClause, $number): array
    {
        $tableAlias = $this->getSortTableName($number);
        //$tableAlias = $this->dbHandler->quoteIdentifier($tableAlias);
        $columnAlias = $this->getSortColumnName($number);

        $query->select(
            $query->alias(
                $this->dbHandler->quoteColumn('name', $tableAlias),
                $columnAlias
            )
        );

        return [$columnAlias];
    }

    /**
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     * @param int $number
     * @param array $languageSettings
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function applyJoin(
        SelectQuery $query,
        SortClause $sortClause,
        $number,
        array $languageSettings
    ): void {
        $tableAlias = $this->getSortTableName($number);
        //$tableAlias = $this->dbHandler->quoteIdentifier($tableAlias);

        $query->leftJoin(
            $query->alias(
                $this->dbHandler->quoteTable('ezcontentobject_name'),
                $tableAlias
            ),
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('id', 'ezcontentobject'),
                    $this->dbHandler->quoteColumn('contentobject_id', $tableAlias)
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('current_version', 'ezcontentobject'),
                    $this->dbHandler->quoteColumn('content_version', $tableAlias)
                ),
                $this->getLanguageCondition($query, $languageSettings, $tableAlias)
            )
        );
    }

    /**
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param array $languageSettings
     * @param string $contentNameTableAlias
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @return \Doctrine\DBAL\Query\Expression\CompositeExpression|string
     */
    protected function getLanguageCondition(
        SelectQuery $query,
        array $languageSettings,
        string $contentNameTableAlias
    ) {
        // 1. Use main language(s) by default
        if (empty($languageSettings['languages'])) {
            return $query->expr->gt(
                $query->expr->bitAnd(
                    $this->dbHandler->quoteColumn('initial_language_id', 'ezcontentobject'),
                    $this->dbHandler->quoteColumn('language_id', $contentNameTableAlias)
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
                    $this->dbHandler->quoteColumn('language_id', $contentNameTableAlias)
                )
            ),
            $query->bindValue(1, null, PDO::PARAM_INT)
        );
        $rightSide = $query->expr->bitAnd(
            $this->dbHandler->quoteColumn('language_id', $contentNameTableAlias),
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
                        $this->dbHandler->quoteColumn('language_id', $contentNameTableAlias)
                    )
                ),
                $query->bindValue($languageId, null, PDO::PARAM_INT)
            );
            $addToRightSide = $query->expr->bitAnd(
                $this->dbHandler->quoteColumn('language_id', $contentNameTableAlias),
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
                    $this->dbHandler->quoteColumn('language_id', $contentNameTableAlias)
                ),
                $query->bindValue(0, null, PDO::PARAM_INT)
            ),
            $query->expr->lt($leftSide, $rightSide)
        );
    }
}
