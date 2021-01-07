<?php

namespace Netgen\EzPlatformSearchExtra\API\Values\Content\Search;

use eZ\Publish\API\Repository\Values\Content\Query as BaseQuery;

class Query extends BaseQuery
{
    /**
     * List of additional fields that should be
     * extracted from the Solr document for each hit.
     *
     * @var string[]
     */
    public $extraFields;
}
