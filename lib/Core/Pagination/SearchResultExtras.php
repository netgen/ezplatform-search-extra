<?php

namespace Netgen\EzPlatformSearchExtra\Core\Pagination;

/**
 * Defines access to extra information of the search query result.
 */
interface SearchResultExtras
{
    /**
     * The facets for the search query.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\Facet[]
     */
    public function getFacets();

    /**
     * The maximum score for the search query.
     *
     * @return float
     */
    public function getMaxScore();

    /**
     * The duration of the search processing in ms.
     *
     * Note: this will be available only if the query is executed.
     *
     * @return int|null
     */
    public function getTime();
}
