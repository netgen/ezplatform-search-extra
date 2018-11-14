<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\Query\Common\SortClauseVisitor;

use EzSystems\EzPlatformSolrSearchEngine\Query\SortClauseVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\SortClause\Random as RandomSortClause;

/**
 * Visits the sortClause tree into a Solr query.
 */
class Random extends SortClauseVisitor
{
    /**
     * Check if visitor is applicable to current sortClause.
     *
     * @param SortClause $sortClause
     *
     * @return bool
     */
    public function canVisit(SortClause $sortClause)
    {
        return $sortClause instanceof RandomSortClause;
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @param SortClause $sortClause
     *
     * @return string
     */
    public function visit(SortClause $sortClause)
    {
        return 'random_' . $sortClause->targetData->seed . $this->getDirection($sortClause);
    }
}
