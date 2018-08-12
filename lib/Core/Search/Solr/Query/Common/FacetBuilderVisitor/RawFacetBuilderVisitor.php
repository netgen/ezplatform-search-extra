<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\Query\Common\FacetBuilderVisitor;

use EzSystems\EzPlatformSolrSearchEngine\Query\FacetBuilderVisitor;
use EzSystems\EzPlatformSolrSearchEngine\Query\FacetFieldVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
use Netgen\EzPlatformSearchExtra\Core\Search\Solr\API\Facet\RawFacet;
use Netgen\EzPlatformSearchExtra\Core\Search\Solr\API\Facet\RawFacetBuilder;

/**
 * Visits the ContentType facet builder.
 */
class RawFacetBuilderVisitor extends FacetBuilderVisitor implements FacetFieldVisitor
{
    /**
     * {@inheritdoc}.
     */
    public function mapField($field, array $data, FacetBuilder $facetBuilder)
    {
        return new RawFacet([
            'name' => $facetBuilder->name,
            'data' => $data,
        ]);
    }

    /**
     * {@inheritdoc}.
     */
    public function canVisit(FacetBuilder $facetBuilder)
    {
        return $facetBuilder instanceof RawFacetBuilder;
    }

    /**
     * {@inheritdoc}.
     */
    public function visitBuilder(FacetBuilder $facetBuilder, $fieldId)
    {
        /** @var $facetBuilder \Netgen\EzPlatformSearchExtra\Core\Search\Solr\API\Facet\RawFacetBuilder */
        return $facetBuilder->parameters;
    }
}
