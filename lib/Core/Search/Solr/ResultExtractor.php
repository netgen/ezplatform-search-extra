<?php

declare(strict_types=1);

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr;

use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor as BaseResultExtractor;
use Netgen\EzPlatformSearchExtra\Core\Search\Solr\API\FacetBuilder\RawFacetBuilder;

/**
 * This DocumentMapper implementation adds support for handling RawFacetBuilders.
 *
 * @see \Netgen\EzPlatformSearchExtra\Core\Search\Solr\API\Facet\RawFacetBuilder
 */
abstract class ResultExtractor Extends BaseResultExtractor
{
    public function extract(
        $data,
        array $facetBuilders = [],
        array $aggregations = [],
        array $languageFilter = []
    ): SearchResult {
        $searchResult = $this->extractSearchResult($data, $facetBuilders);

        if (!isset($data->facets) || $data->facets->count === 0) {
            return $searchResult;
        }

        foreach ($this->filterNewFacetBuilders($facetBuilders) as $facetBuilder) {
            $identifier = \spl_object_hash($facetBuilder);

            $searchResult->facets[] = $this->facetBuilderVisitor->mapField(
                $identifier,
                [$data->facets->{$identifier}],
                $facetBuilder
            );
        }

        return $searchResult;
    }

    /**
     * Extract the base search result.
     *
     * @param mixed $data
     * @param \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder[] $facetBuilders
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    abstract protected function extractSearchResult($data, array $facetBuilders = []): SearchResult;

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder[] $facetBuilders
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder[]
     */
    private function filterNewFacetBuilders(array $facetBuilders): array
    {
        return array_filter(
            $facetBuilders,
            function ($facetBuilder) {
                return $facetBuilder instanceof RawFacetBuilder;
            }
        );
    }
}
