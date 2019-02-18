<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\Query\Common\FacetBuilderVisitor;

use EzSystems\EzPlatformSolrSearchEngine\Query\FacetBuilderVisitor;
use EzSystems\EzPlatformSolrSearchEngine\Query\FacetFieldVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
use Netgen\EzPlatformSearchExtra\Core\Search\Solr\API\Facet\RawFacet;
use Netgen\EzPlatformSearchExtra\Core\Search\Solr\API\FacetBuilder\RawFacetBuilder;
use Netgen\EzPlatformSearchExtra\Core\Search\Solr\Query\Common\FacetBuilderVisitor\RawFacetBuilderVisitor\DomainVisitor;

/**
 * Visits the RawFacetBuilder.
 */
class RawFacetBuilderVisitor extends FacetBuilderVisitor implements FacetFieldVisitor
{
    /**
     * @var \Netgen\EzPlatformSearchExtra\Core\Search\Solr\Query\Common\FacetBuilderVisitor\RawFacetBuilderVisitor\DomainVisitor
     */
    private $domainVisitor;

    /**
     * @param \Netgen\EzPlatformSearchExtra\Core\Search\Solr\Query\Common\FacetBuilderVisitor\RawFacetBuilderVisitor\DomainVisitor $domainVisitor
     */
    public function __construct(DomainVisitor $domainVisitor)
    {
        $this->domainVisitor = $domainVisitor;
    }

    public function mapField($field, array $data, FacetBuilder $facetBuilder)
    {
        return new RawFacet([
            'name' => $facetBuilder->name,
            'data' => \reset($data),
        ]);
    }

    public function canVisit(FacetBuilder $facetBuilder)
    {
        return $facetBuilder instanceof RawFacetBuilder;
    }

    public function visitBuilder(FacetBuilder $facetBuilder, $fieldId)
    {
        /** @var $facetBuilder \Netgen\EzPlatformSearchExtra\Core\Search\Solr\API\FacetBuilder\RawFacetBuilder */
        $parameters = $facetBuilder->parameters;

        if ($facetBuilder->domain !== null) {
            $parameters['domain'] = $this->domainVisitor->visit($facetBuilder->domain);
        }

        return $parameters;
    }
}
