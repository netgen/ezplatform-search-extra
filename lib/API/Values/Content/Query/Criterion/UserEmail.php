<?php

namespace Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications;

/**
 * UserEmail criterion matches Content based on matching User email.
 */
class UserEmail extends Criterion
{
    /**
     * @param string $operator
     * @param string|string[] $value
     */
    public function __construct($operator, $value)
    {
        parent::__construct(null, $operator, $value);
    }

    public function getSpecifications(): array
    {
        return array(
            new Specifications(Operator::IN, Specifications::FORMAT_ARRAY),
            new Specifications(Operator::EQ, Specifications::FORMAT_SINGLE),
            new Specifications(Operator::LIKE, Specifications::FORMAT_SINGLE),
        );
    }
}
