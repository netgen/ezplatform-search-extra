<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\Query\Common\FacetBuilderVisitor\RawFacetBuilderVisitor;

use Netgen\EzPlatformSearchExtra\Core\Search\Solr\API\FacetBuilder\RawFacetBuilder\Domain;

abstract class DomainVisitor
{
    /**
     * @param \Netgen\EzPlatformSearchExtra\Core\Search\Solr\API\FacetBuilder\RawFacetBuilder\Domain $domain
     *
     * @return bool
     */
    abstract public function accept(Domain $domain);

    /**
     * @param \Netgen\EzPlatformSearchExtra\Core\Search\Solr\API\FacetBuilder\RawFacetBuilder\Domain $domain
     *
     * @return array
     */
    abstract public function visit(Domain $domain);
}
