<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr;

use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor as BaseResultExtractor;
use Netgen\EzPlatformSearchExtra\Core\Search\Solr\API\Facet\RawFacetBuilder;

/**
 * This DocumentMapper implementation adds support for handling RawFacetBuilders.
 *
 * @see \Netgen\EzPlatformSearchExtra\Core\Search\Solr\API\Facet\RawFacetBuilder
 */
final class ResultExtractor Extends BaseResultExtractor
{
    /**
     * @var \EzSystems\EzPlatformSolrSearchEngine\ResultExtractor
     */
    private $nativeResultExtractor;

    /** @noinspection PhpMissingParentConstructorInspection */
    /** @noinspection MagicMethodsValidityInspection */
    /**
     * @param \EzSystems\EzPlatformSolrSearchEngine\ResultExtractor $nativeResultExtractor
     */
    public function __construct(BaseResultExtractor $nativeResultExtractor)
    {
        $this->nativeResultExtractor = $nativeResultExtractor;
    }

    public function extract($data, array $facetBuilders = [])
    {
        $searchResult = $this->nativeResultExtractor->extract($data, $facetBuilders);

        if (!isset($data->facets)) {
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

    public function extractHit($hit)
    {
        return $this->nativeResultExtractor->extractHit($hit);
    }
}
