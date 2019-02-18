<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\Query\Common\FacetBuilderVisitor\RawFacetBuilderVisitor\DomainVisitor;

use OutOfBoundsException;
use Netgen\EzPlatformSearchExtra\Core\Search\Solr\API\FacetBuilder\RawFacetBuilder\Domain;
use Netgen\EzPlatformSearchExtra\Core\Search\Solr\Query\Common\FacetBuilderVisitor\RawFacetBuilderVisitor\DomainVisitor;

class Aggregate extends DomainVisitor
{
    /**
     * @var \Netgen\EzPlatformSearchExtra\Core\Search\Solr\Query\Common\FacetBuilderVisitor\RawFacetBuilderVisitor\DomainVisitor[]
     */
    private $visitors = [];

    /**
     * @param \Netgen\EzPlatformSearchExtra\Core\Search\Solr\Query\Common\FacetBuilderVisitor\RawFacetBuilderVisitor\DomainVisitor[] $visitors
     */
    public function __construct(array $visitors = [])
    {
        foreach ($visitors as $visitor) {
            $this->addVisitor($visitor);
        }
    }

    public function addVisitor(DomainVisitor $visitor)
    {
        $this->visitors[] = $visitor;
    }

    public function accept(Domain $domain)
    {
        return true;
    }

    public function visit(Domain $domain)
    {
        foreach ($this->visitors as $visitor) {
            if ($visitor->accept($domain)) {
                return $visitor->visit($domain);
            }
        }

        throw new OutOfBoundsException('No visitor found for the given domain');
    }
}
