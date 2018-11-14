<?php

namespace Netgen\EzPlatformSearchExtra\API\Values\Content\Query\SortClause;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\SortClause\Target\RandomTarget;

/**
 * Sets sort random on a content query.
 */
class Random extends SortClause
{
    /**
     * Constructs a new Random SortClause.
     *
     * @param string $sortDirection
     */
    public function __construct($seed, $sortDirection = Query::SORT_ASC)
    {
        parent::__construct('random', $sortDirection, new RandomTarget($seed));
    }
}
