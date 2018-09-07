<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr;

use EzSystems\EzPlatformSolrSearchEngine\Query\FacetFieldVisitor;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor as BaseResultExtractor;
use Netgen\EzPlatformSearchExtra\Core\Search\Solr\API\FacetBuilder\RawFacetBuilder;

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
     * @param \EzSystems\EzPlatformSolrSearchEngine\Query\FacetFieldVisitor $facetBuilderVisitor
     */
    public function __construct(
        BaseResultExtractor $nativeResultExtractor,
        FacetFieldVisitor $facetBuilderVisitor
    ) {
        $this->nativeResultExtractor = $nativeResultExtractor;
        $this->facetBuilderVisitor = $facetBuilderVisitor;
    }

    public function extract($data, array $facetBuilders = [])
    {
        $searchResult = $this->nativeResultExtractor->extract($data, $facetBuilders);

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
