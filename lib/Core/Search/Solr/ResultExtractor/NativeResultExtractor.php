<?php

declare(strict_types=1);

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\ResultExtractor;

use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\ValueObject;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointRegistry;
use EzSystems\EzPlatformSolrSearchEngine\Query\FacetFieldVisitor;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor as BaseResultExtractor;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor;
use Netgen\EzPlatformSearchExtra\Core\Search\Solr\ResultExtractor;

/**
 * Native Result Extractor extracts the value object from the data returned by the Solr backend.
 */
final class NativeResultExtractor Extends ResultExtractor
{
    /**
     * @var \EzSystems\EzPlatformSolrSearchEngine\ResultExtractor
     */
    private $nativeResultExtractor;

    public function __construct(
        BaseResultExtractor $nativeResultExtractor,
        FacetFieldVisitor $facetBuilderVisitor,
        AggregationResultExtractor $aggregationResultExtractor,
        EndpointRegistry $endpointRegistry
    ) {
        $this->nativeResultExtractor = $nativeResultExtractor;

        parent::__construct($facetBuilderVisitor, $aggregationResultExtractor, $endpointRegistry);
    }

    protected function extractSearchResult(
        $data,
        array $facetBuilders = [],
        array $aggregations = [],
        array $languageFilter = []
    ): SearchResult {
        return $this->nativeResultExtractor->extract(
            $data,
            $facetBuilders,
            $aggregations,
            $languageFilter
        );
    }

    public function extractHit($hit): ValueObject
    {
        return $this->nativeResultExtractor->extractHit($hit);
    }
}
