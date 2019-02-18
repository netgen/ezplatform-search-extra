<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\API\FacetBuilder\RawFacetBuilder\Domain;

use Netgen\EzPlatformSearchExtra\Core\Search\Solr\API\FacetBuilder\RawFacetBuilder\Domain;

/**
 * BlockChildren block-join domain for RawFacetBuilder.
 *
 * @see \Netgen\EzPlatformSearchExtra\Core\Search\Solr\API\FacetBuilder\RawFacetBuilder
 */
class BlockChildren extends Domain
{
    /**
     * @var string
     */
    public $parentDocumentIdentifier;

    /**
     * @var string
     */
    public $childDocumentIdentifier;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion|null
     */
    public $filter;
}
