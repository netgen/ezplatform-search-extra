<?php

namespace Netgen\EzPlatformSearchExtra\API\Values\Content\Search;

use eZ\Publish\API\Repository\Values\Content\Search\SearchResult as BaseSearchResult;

class SearchResult extends BaseSearchResult
{
    /**
     * List of suggestions after spell checking.
     *
     * @var \Netgen\EzPlatformSearchExtra\API\Values\Content\Search\SpellCheckSuggestion[]
     */
    public $spellCheckSuggestions = array();
}
