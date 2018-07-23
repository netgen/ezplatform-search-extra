<?php

namespace Netgen\EzPlatformSearchExtra\API\Values\Content\Query\FacetBuilder;

use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;

/**
 * Builds a custom field facet.
 */
class CustomFieldFacetBuilder extends FacetBuilder
{
    /**
     * Sort by facet count descending.
     */
    const COUNT_DESC = 'count_descending';

    /**
     * Sort by facet term ascending.
     */
    const TERM_ASC = 'term_ascending';

    /**
     * Name of the field in the Solr backend.
     *
     * @var string
     */
    public $fieldName;

    /**
     * The sort order of the terms.
     *
     * One of CustomFieldFacetBuilder::COUNT_DESC, CustomFieldFacetBuilder::TERM_ASC.
     *
     * @var mixed
     */
    public $sort;
}
