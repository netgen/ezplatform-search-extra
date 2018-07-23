<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\Query\Content\CriterionVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\SubdocumentQuery as SubdocumentQueryCriterion;

/**
 * Visits the SubdocumentQuery criterion.
 *
 * @see \Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\SubdocumentQuery
 */
final class SubdocumentQuery extends CriterionVisitor
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

    public function canVisit(Criterion $criterion)
    {
        return $criterion instanceof SubdocumentQueryCriterion;
    }

    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null)
    {
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion $query */
        $query = $criterion->value;
        $identifier = $criterion->target;

        $condition = $this->escapeQuote(
            $this->subdocumentQueryCriterionVisitor->visit($query)
        );

        $condition = str_replace('/', '\\/', $condition);

        return "{!parent which='document_type_id:content' v='document_type_id:{$identifier} AND {$condition}'}";
    }
}
