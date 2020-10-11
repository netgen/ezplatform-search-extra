<?php

declare(strict_types=1);

namespace Netgen\EzPlatformSearchExtra\Core\Search\Legacy\Query\Common\SortClauseHandler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\ContentName as ContentNameSortClause;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;

class ContentName extends SortClauseHandler
{
    protected $languageHandler;

    public function __construct(Connection $connection, LanguageHandler $languageHandler)
    {
        parent::__construct($connection);

        $this->languageHandler = $languageHandler;
    }

    public function accept(SortClause $sortClause): bool
    {
        return $sortClause instanceof ContentNameSortClause;
    }

    public function applySelect(QueryBuilder $query, SortClause $sortClause, int $number): array
    {
        $tableAlias = $this->getSortTableName($number);
        $tableAlias = $this->connection->quoteIdentifier($tableAlias);
        $columnAlias = $this->getSortColumnName($number);

        $query->addSelect(sprintf('%s.name AS %s', $tableAlias, $columnAlias));

        return [$columnAlias];
    }

    /**
     * @param \Doctrine\DBAL\Query\QueryBuilder $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     * @param int $number
     * @param array $languageSettings
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function applyJoin(
        QueryBuilder $query,
        SortClause $sortClause,
        int $number,
        array $languageSettings
    ): void {
        $tableAlias = $this->getSortTableName($number);
        $tableAlias = $this->connection->quoteIdentifier($tableAlias);

        $query->leftJoin(
            'c',
            Gateway::CONTENT_NAME_TABLE,
            $tableAlias,
            $query->expr()->and(
                $query->expr()->eq('c.id', $tableAlias . '.contentobject_id'),
                $query->expr()->eq('c.current_version', $tableAlias . '.content_version'),
                $this->getLanguageCondition($query, $languageSettings, $tableAlias)
            )
        );
    }

    /**
     * @param \Doctrine\DBAL\Query\QueryBuilder $query
     * @param array $languageSettings
     * @param string $contentNameTableName
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @return \Doctrine\DBAL\Query\Expression\CompositeExpression|string
     */
    protected function getLanguageCondition(
        QueryBuilder $query,
        array $languageSettings,
        string $contentNameTableName
    ) {
        // 1. Use main language(s) by default
        if (empty($languageSettings['languages'])) {
            return $query->expr()->gt(
                $this->dbPlatform->getBitAndComparisonExpression(
                    'c.initial_language_id',
                    $contentNameTableName . '.language_id'
                ),
                $query->createNamedParameter(0, ParameterType::INTEGER)
            );
        }

        // 2. Otherwise use prioritized languages
        $leftSide = $this->dbPlatform->getBitAndComparisonExpression(
            sprintf(
                'c.language_mask - %s',
                $this->dbPlatform->getBitAndComparisonExpression(
                    'c.language_mask',
                    $contentNameTableName . '.language_id'
                )
            ),
            $query->createNamedParameter(1, ParameterType::INTEGER)
        );
        $rightSide = $this->dbPlatform->getBitAndComparisonExpression(
            $contentNameTableName . '.language_id',
            $query->createNamedParameter(1, ParameterType::INTEGER)
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
                        $contentNameTableName . '.language_id'
                    )
                ),
                $query->createNamedParameter($languageId, ParameterType::INTEGER)
            );
            $addToRightSide = $this->dbPlatform->getBitAndComparisonExpression(
                $contentNameTableName . '.language_id',
                $query->createNamedParameter($languageId, ParameterType::INTEGER)
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

        return $query->expr()->and(
            $query->expr()->gt(
                $this->dbPlatform->getBitAndComparisonExpression(
                    'c.language_mask',
                    $contentNameTableName . '.language_id'
                ),
                $query->createNamedParameter(0, ParameterType::INTEGER)
            ),
            $query->expr()->lt($leftSide, $rightSide)
        );
    }
}
