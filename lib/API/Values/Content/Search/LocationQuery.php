<?php

namespace Netgen\EzPlatformSearchExtra\API\Values\Content\Search;

use eZ\Publish\API\Repository\Values\Content\LocationQuery as BaseLocationQuery;

class LocationQuery extends BaseLocationQuery
{
    /**
     * List of additional fields that should be
     * extracted from the Solr document for each hit.
     *
     * @var string[]
     */
    public $extraFields;
}
