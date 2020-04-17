<?php

namespace Netgen\EzPlatformSearchExtra\Core\Pagination;

/**
 * Defines access to extra information of the search query result.
 */
interface SearchResultExtras
{
    /**
     * Return facets for the search query.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\Facet[]
     */
    public function getFacets();

    /**
     * Return maximum score for the search query.
     *
     * @return float
     */
    public function getMaxScore();

    /**
     * Return suggestion object for the search query.
     *
     * @return \Netgen\EzPlatformSearchExtra\API\Values\Content\Search\Suggestion
     */
    public function getSuggestion();

    /**
     * Return duration of the search query processing in milliseconds.
     *
     * Note: this will be available only if the query is executed.
     *
     * @return int|null
     */
    public function getTime();
}
