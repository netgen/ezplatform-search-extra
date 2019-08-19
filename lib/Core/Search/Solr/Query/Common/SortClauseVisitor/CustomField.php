<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\Query\Common\SortClauseVisitor;

use EzSystems\EzPlatformSolrSearchEngine\Query\SortClauseVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use \Netgen\EzPlatformSearchExtra\API\Values\Content\Query\SortClause\CustomField as CustomFieldSortClause;

class CustomField extends SortClauseVisitor
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
        return $sortClause instanceof CustomFieldSortClause;
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
        return $sortClause->target . $this->getDirection($sortClause);
    }
}
