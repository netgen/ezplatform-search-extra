<?php

namespace Netgen\EzPlatformSearchExtra\API\Values\Content\Query\SortClause;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

/**
 * Sets sort direction on the content score for a content query.
 */
class Score extends SortClause
{
    /**
     * Constructs a new Score SortClause.
     *
     * @param string $sortDirection
     */
    public function __construct($sortDirection = Query::SORT_ASC)
    {
        parent::__construct('score', $sortDirection);
    }
}
