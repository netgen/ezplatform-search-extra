<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr;

use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor as BaseResultExtractor;
use Netgen\EzPlatformSearchExtra\Core\Search\Solr\API\FacetBuilder\RawFacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Query;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Search\Query as ExtraQuery;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Search\LocationQuery as ExtraLocationQuery;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Search\SearchHit;

/**
 * This DocumentMapper implementation adds support for handling RawFacetBuilders.
 *
 * @see \Netgen\EzPlatformSearchExtra\Core\Search\Solr\API\Facet\RawFacetBuilder
 */
abstract class ResultExtractor Extends BaseResultExtractor
{
    public function extract($data, array $facetBuilders = [], ?Query $query = null)
    {
        $searchResult = $this->extractSearchResult($data, $facetBuilders);

        foreach ($searchResult->searchHits as $key => $searchHit) {
            $searchResult->searchHits[$key] = new SearchHit(get_object_vars($searchHit));
            $searchResult->searchHits[$key]->extraFields = [];

            if (($query instanceof ExtraQuery || $query instanceof ExtraLocationQuery) && is_array($query->extraFields)) {
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
    abstract protected function extractSearchResult($data, array $facetBuilders = []);

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder[] $facetBuilders
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder[]
     */
    private function filterNewFacetBuilders(array $facetBuilders)
    {
        return array_filter(
            $facetBuilders,
            function ($facetBuilder) {
                return $facetBuilder instanceof RawFacetBuilder;
            }
        );
    }

    /**
     * @param mixed $data
     * @param \Netgen\EzPlatformSearchExtra\API\Values\Content\Search\SearchHit $searchHit
     * @param string[] $extraFields
     *
     * @return array
     */
    private function extractExtraFields($data, SearchHit $searchHit, $extraFields)
    {
        $extractedExtraFields = [];
        foreach ($data->response->docs as $doc) {
            if ($doc->document_type_id === 'content' && $doc->content_id_id == $searchHit->valueObject->id
            || $doc->document_type_id === 'location' && $doc->location_id == $searchHit->valueObject->id) {
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
