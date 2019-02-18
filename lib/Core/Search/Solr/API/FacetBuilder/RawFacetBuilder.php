<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\API\FacetBuilder;

use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;

/**
 * RawFacetBuilder provides full Solr JSON facet API.
 */
class RawFacetBuilder extends FacetBuilder
{
    /**
     * Solr JSON facet API params as an array to be encoded with \json_encode().
     *
     * Example:
     *
     * ```php
     *  $facet->parameters = [
     *      'type': 'terms'
     *      'field' => 'genre',
     *      'limit' => 5,
     *  ];
     * ```
     *
     * @var array
     */
    public $parameters;

    /**
     * @var \Netgen\EzPlatformSearchExtra\Core\Search\Solr\API\FacetBuilder\RawFacetBuilder\Domain|null
     */
    public $domain;
}
