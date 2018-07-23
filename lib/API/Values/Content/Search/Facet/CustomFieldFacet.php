<?php

namespace Netgen\EzPlatformSearchExtra\API\Values\Content\Search\Facet;

use eZ\Publish\API\Repository\Values\Content\Search\Facet;

/**
 * Holds custom field facet terms and counts.
 */
class CustomFieldFacet extends Facet
{
    /**
     * An array of terms (key) and counts (value).
     *
     * @var array
     */
    public $entries;
}
