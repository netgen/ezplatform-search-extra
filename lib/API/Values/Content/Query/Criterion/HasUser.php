<?php

namespace Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications;

/**
 * HasUser criterion matches Content based on whether is has matching User or not.
 */
class HasUser extends Criterion
{
    /**
     * @param bool $hasUser
     */
    public function __construct($hasUser)
    {
        parent::__construct(null, null, $hasUser);
    }

    public function getSpecifications(): array
    {
        return [
            new Specifications(
                Operator::EQ,
                Specifications::FORMAT_SINGLE,
                Specifications::TYPE_BOOLEAN
            ),
        ];
    }
}
