<?php

namespace Netgen\EzPlatformSearchExtra\API\Values\Content\Query\SortClause\Target;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\SubdocumentQuery;

final class SubdocumentTarget extends Target
{
    /**
     * Identifier of a targeted Content subdocument.
     *
     * @var string
     */
    public $documentTypeIdentifier;

    /**
     * One of the ScoringMode* constants.
     *
     * @see \Netgen\EzPlatformSearchExtra\API\Values\Content\Query\SortClause\SubdocumentField
     *
     * @var string
     */
    public $scoringMode;

    /**
     * Optional criterion targeting Content subdocument.
     *
     * @var \Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\SubdocumentQuery
     */
    public $subdocumentQuery;

    /**
     * @param string $documentTypeIdentifier
     * @param string $scoringMode
     * @param \Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\SubdocumentQuery $subdocumentQuery
     */
    public function __construct($documentTypeIdentifier, $scoringMode, SubdocumentQuery $subdocumentQuery = null)
    {
        $this->documentTypeIdentifier = $documentTypeIdentifier;
        $this->scoringMode = $scoringMode;
        $this->subdocumentQuery = $subdocumentQuery;
    }
}
