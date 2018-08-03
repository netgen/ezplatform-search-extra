<?php

namespace Netgen\EzPlatformSearchExtra\API\Values\Content\Query\SortClause;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\SubdocumentQuery;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\SortClause\Target\SubdocumentTarget;

/**
 * SubdocumentField sort clause is used to sort Content by field in matched subdocument.
 */
final class SubdocumentField extends SortClause
{
    const ScoringModeNone = 'ScoringModeNone';
    const ScoringModeAverage = 'ScoringModeAvg';
    const ScoringModeMaximum = 'ScoringModeMax';
    const ScoringModeTotal = 'ScoringModeTotal';
    const ScoringModeMinimum = 'ScoringModeMin';

    /**
     * @param string $fieldName
     * @param string $documentTypeIdentifier
     * @param string $scoringMode
     * @param string $sortDirection
     * @param \Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\SubdocumentQuery $subdocumentQuery
     */
    public function __construct(
        $fieldName,
        $documentTypeIdentifier,
        $scoringMode = self::ScoringModeNone,
        $sortDirection = Query::SORT_ASC,
        SubdocumentQuery $subdocumentQuery = null
    ) {
        parent::__construct(
            $fieldName,
            $sortDirection,
            new SubdocumentTarget(
                $documentTypeIdentifier,
                $scoringMode,
                $subdocumentQuery
            )
        );
    }
}
