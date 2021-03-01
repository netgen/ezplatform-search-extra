<?php

declare(strict_types=1);

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor as BaseResultExtractor;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Search\Query as ExtraQuery;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Search\LocationQuery as ExtraLocationQuery;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Search\SearchHit;
use Netgen\EzPlatformSearchExtra\Core\Search\Solr\API\FacetBuilder\RawFacetBuilder;
use function spl_object_hash;

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
        array $languageFilter = [],
        ?Query $query = null
    ): SearchResult {
        $searchResult = $this->extractSearchResult(
            $data,
            $facetBuilders,
            $aggregations,
            $languageFilter
        );

        foreach ($searchResult->searchHits as $key => $searchHit) {
            $searchResult->searchHits[$key] = new SearchHit(get_object_vars($searchHit));
            $searchResult->searchHits[$key]->extraFields = [];

            if ($query instanceof ExtraQuery || $query instanceof ExtraLocationQuery) {
                $searchResult->searchHits[$key]->extraFields = $this->extractExtraFields(
                    $data,
                    $searchResult->searchHits[$key],
                    $query->extraFields
                );
            }
        }

        if (!isset($data->facets) || $data->facets->count === 0) {
            return $searchResult;
        }

        foreach ($this->filterNewFacetBuilders($facetBuilders) as $facetBuilder) {
            $identifier = spl_object_hash($facetBuilder);

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
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Aggregation[] $aggregations
     * @param array $languageFilter
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    abstract protected function extractSearchResult(
        $data,
        array $facetBuilders = [],
        array $aggregations = [],
        array $languageFilter = []
    ): SearchResult;

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder[] $facetBuilders
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder[]
     */
    private function filterNewFacetBuilders(array $facetBuilders): array
    {
        return array_filter(
            $facetBuilders,
            static function ($facetBuilder) {
                return $facetBuilder instanceof RawFacetBuilder;
            }
        );
    }

    /**
     * @param mixed $data
     * @param \Netgen\EzPlatformSearchExtra\API\Values\Content\Search\SearchHit $searchResult
     * @param string[] $extraFields
     */
    private function extractExtraFields($data, SearchHit $searchHit, $extraFields)
    {
        $extractedExtraFields = [];
        foreach ($data->response->docs as $doc) {
            if ($doc->document_type_id === 'content' && $doc->content_id_id == $searchHit->valueObject->id
            || $doc->document_type_id === 'location' && $doc->location_id_id == $searchHit->valueObject->mainLocationId) {
                foreach ($extraFields as $extraField) {
                    if (property_exists($doc, $extraField)) {
                        $extractedExtraFields[$extraField] = $doc->{$extraField};
                    }
                }
            }
        }

        return $extractedExtraFields;
    }
}
