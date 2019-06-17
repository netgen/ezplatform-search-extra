<?php

namespace Netgen\EzPlatformSearchExtra\API\Values\Content\Search;

use eZ\Publish\API\Repository\Values\Content\Search\SearchResult as BaseSearchResult;

class SearchResult extends BaseSearchResult
{
    /**
     * Contains suggestion for misspelled words.
     *
     * @var \Netgen\EzPlatformSearchExtra\API\Values\Content\Search\Suggestion
     */
    public $suggestion;
}
