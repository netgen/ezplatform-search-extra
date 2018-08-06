<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\Query\Content\CriterionVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\LocationQuery as LocationQueryCriterion;

/**
 * Visits the LocationQuery criterion.
 *
 * @see \Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\LocationQuery
 */
final class LocationQuery extends CriterionVisitor
{
    /**
     * @var \EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor
     */
    private $locationQueryCriterionVisitor;

    /**
     * @param \EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor $locationQueryCriterionVisitor
     */
    public function __construct(CriterionVisitor $locationQueryCriterionVisitor)
    {
        $this->locationQueryCriterionVisitor = $locationQueryCriterionVisitor;
    }

    public function canVisit(Criterion $criterion)
    {
        return $criterion instanceof LocationQueryCriterion;
    }

    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null)
    {
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion $filter */
        $filter = $criterion->value;

        $condition = $this->escapeQuote(
            $this->locationQueryCriterionVisitor->visit($filter)
        );

        $condition = str_replace('/', '\\/', $condition);

        return "{!parent which='document_type_id:content' v='document_type_id:location AND {$condition}'}";
    }
}
