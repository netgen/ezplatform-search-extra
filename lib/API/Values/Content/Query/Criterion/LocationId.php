<?php

namespace Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications;

/**
 * A criterion that matches Location based on its ID.
 */
class LocationId extends Criterion
{
    /**
     * @param string $operator One of the Operator constants
     * @param int|int[]|string|string[] $value One or more Location IDs that must be matched
     */
    public function __construct($operator, $value)
    {
        parent::__construct(null, $operator, $value);
    }

    public function getSpecifications()
    {
        return [
            new Specifications(Operator::EQ, Specifications::FORMAT_SINGLE, Specifications::TYPE_INTEGER),
            new Specifications(Operator::IN, Specifications::FORMAT_ARRAY, Specifications::TYPE_INTEGER),
            new Specifications(Operator::GT, Specifications::FORMAT_SINGLE, Specifications::TYPE_INTEGER),
            new Specifications(Operator::GTE, Specifications::FORMAT_SINGLE, Specifications::TYPE_INTEGER),
            new Specifications(Operator::LT, Specifications::FORMAT_SINGLE, Specifications::TYPE_INTEGER),
            new Specifications(Operator::LTE, Specifications::FORMAT_SINGLE, Specifications::TYPE_INTEGER),
            new Specifications(Operator::BETWEEN, Specifications::FORMAT_ARRAY, Specifications::TYPE_INTEGER, 2),
        ];
    }

    /**
     * @inheritdoc
     *
     * @deprecated since 7.2, will be removed in 8.0. Use the constructor directly instead.
     */
    public static function createFromQueryBuilder($target, $operator, $value)
    {
        @trigger_error(
            'The ' . __METHOD__ . ' method is deprecated since version 7.2 and will be removed in 8.0.',
            E_USER_DEPRECATED
        );

        return new self($operator, $value);
    }
}
