<?php

declare(strict_types=1);

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\Query\Common\SortClauseVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use EzSystems\EzPlatformSolrSearchEngine\Query\SortClauseVisitor;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\SortClause\ContentName as ContentNameClause;

class ContentName extends SortClauseVisitor
{
    public function canVisit(SortClause $sortClause): bool
    {
        return $sortClause instanceof ContentNameClause;
    }

    public function visit(SortClause $sortClause): string
    {
        return 'ng_content_name_s' . $this->getDirection($sortClause);
    }
}
