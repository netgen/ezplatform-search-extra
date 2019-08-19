<?php

namespace Netgen\EzPlatformSearchExtra\API\Values\Content\Query\SortClause;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\SubdocumentQuery;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\SortClause\Target\SubdocumentTarget;

/**
 * CustomField sort clause is used to sort Content by custom field indexed for this content.
 */
final class CustomField extends SortClause
{
    /**
     * @param string $fieldName
     * @param string $sortDirection
     */
    public function __construct($fieldName, $sortDirection = Query::SORT_ASC)
    {
        parent::__construct($fieldName, $sortDirection);
    }
}
