<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\ResultExtractor;

use EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointRegistry;
use EzSystems\EzPlatformSolrSearchEngine\Query\FacetFieldVisitor;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor as BaseResultExtractor;
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
        EndpointRegistry $endpointRegistry
    ) {
        $this->nativeResultExtractor = $nativeResultExtractor;

        parent::__construct($facetBuilderVisitor, $endpointRegistry);
    }

    protected function extractSearchResult($data, array $facetBuilders = [])
    {
        return $this->nativeResultExtractor->extract($data, $facetBuilders);
    }

    public function extractHit($hit)
    {
        return $this->nativeResultExtractor->extractHit($hit);
    }
}
