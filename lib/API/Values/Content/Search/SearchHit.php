<?php

namespace Netgen\EzPlatformSearchExtra\API\Values\Content\Search;

use eZ\Publish\API\Repository\Values\Content\Search\SearchHit as BaseSearchHit;

class SearchHit extends BaseSearchHit
{
    /**
     * Additional fields from Solr document.
     *
     * @var array
     */
    public $extraFields;
}
