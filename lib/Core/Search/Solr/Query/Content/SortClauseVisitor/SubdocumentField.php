<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\Query\Content\SortClauseVisitor;

use EzSystems\EzPlatformSolrSearchEngine\Query\SortClauseVisitor;
use EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\SubdocumentQuery;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\SortClause\SubdocumentField as SubdocumentFieldCriterion;
use RuntimeException;

class SubdocumentField extends SortClauseVisitor
{
    /**
     * @var \EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor
     */
    private $subdocumentQueryCriterionVisitor;

    /**
     * @param \EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor $subdocumentQueryCriterionVisitor
     */
    public function __construct(CriterionVisitor $subdocumentQueryCriterionVisitor)
    {
        $this->subdocumentQueryCriterionVisitor = $subdocumentQueryCriterionVisitor;
    }

    public function canVisit(SortClause $sortClause)
    {
        return $sortClause instanceof SubdocumentFieldCriterion;
    }

    public function visit(SortClause $sortClause)
    {
        /** @var \Netgen\EzPlatformSearchExtra\API\Values\Content\Query\SortClause\Target\SubdocumentTarget $target */
        $target = $sortClause->targetData;
        $condition = "document_type_id:{$target->documentTypeIdentifier}";

        if ($target->subdocumentQuery instanceof SubdocumentQuery) {
            /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion $filter */
            $filter = $target->subdocumentQuery->value;
            $queryCondition = $this->subdocumentQueryCriterionVisitor->visit($filter);
            $queryCondition = $this->escapeQuote($queryCondition);

            $condition .= ' AND ' . $queryCondition;
        }

        $condition .= " AND {!func}{$sortClause->target}";
        $scoringMode = $this->resolveScoringMode($target->scoringMode);

        return "{!parent which='document_type_id:content' score='{$scoringMode}' v='{$condition}'}" . $this->getDirection($sortClause);
    }

    private function resolveScoringMode($mode)
    {
        switch ($mode) {
            case SubdocumentFieldCriterion::ScoringModeNone:
                return 'none';
            case SubdocumentFieldCriterion::ScoringModeAverage:
                return 'avg';
            case SubdocumentFieldCriterion::ScoringModeMaximum:
                return 'max';
            case SubdocumentFieldCriterion::ScoringModeTotal:
                return 'total';
            case SubdocumentFieldCriterion::ScoringModeMinimum:
                return 'min';
        }

        throw new RuntimeException(
            "Scoring mode '{$mode}' is not handled"
        );
    }

    /**
     * Escapes given $string for wrapping inside single or double quotes.
     *
     * Does not include quotes in the returned string, this needs to be done by the consumer code.
     *
     * @param string $string
     * @param bool $doubleQuote
     *
     * @return string
     */
    private function escapeQuote($string, $doubleQuote = false)
    {
        $pattern = ($doubleQuote ? '/("|\\\)/' : '/(\'|\\\)/');

        return preg_replace($pattern, '\\\$1', $string);
    }
}
