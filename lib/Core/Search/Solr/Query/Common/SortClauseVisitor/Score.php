<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\Query\Common\SortClauseVisitor;

use EzSystems\EzPlatformSolrSearchEngine\Query\SortClauseVisitor;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\SortClause as SortClauseExtra;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

class Score extends SortClauseVisitor
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
        return $sortClause instanceof SortClauseExtra\Score;
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
        return 'score' . $this->getDirection($sortClause);
    }
}
