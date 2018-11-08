<?php

namespace Netgen\EzPlatformSearchExtra\API\Values\Content\Query\SortClause\Target;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target;

/**
 * Struct that stores extra target information for a RandomTarget object.
 */
class RandomTarget extends Target
{
    public $seed;

    public function __construct($seed)
    {
        $this->seed = $seed;
    }
}
