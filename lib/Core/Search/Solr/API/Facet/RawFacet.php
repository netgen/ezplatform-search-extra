<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\API\Facet;

use eZ\Publish\API\Repository\Values\Content\Search\Facet;

/**
 * Holds facet data for RawFacetBuilder.
 *
 * @see \Netgen\EzPlatformSearchExtra\Core\Search\Solr\API\Facet\RawFacetBuilder
 */
class RawFacet extends Facet
{
    /**
     * Facet data as \stdObject instance from \json_decode() on raw Solr search result.
     *
     * Example:
     *
     * ```php
     * $averagePrice = $facet->data->prices->allBuckets->average;
     * ```
     *
     * @var mixed
     */
    public $data;
}
