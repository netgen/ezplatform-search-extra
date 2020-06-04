<?php

namespace Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications;

/**
 * LocationQuery criterion is used to query Location subdocuments in Content search.
 */
class LocationQuery extends Criterion
{
    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $filter
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(Criterion $filter)
    {
        parent::__construct(null, null, $filter);
    }

    public function getSpecifications(): array
    {
        return [
            new Specifications(Operator::EQ, Specifications::FORMAT_SINGLE),
        ];
    }
}
