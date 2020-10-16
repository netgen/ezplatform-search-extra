<?php

declare(strict_types=1);

namespace Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications;

/**
 * A criterion that matches Content matched translation's Content name.
 */
class ContentName extends Criterion
{
    /**
     * @param string $operator One of the Operator constants
     * @param string|string[] $value One or more Content names that must be matched
     */
    public function __construct(string $operator, $value)
    {
        parent::__construct(null, $operator, $value);
    }

    public function getSpecifications(): array
    {
        return [
            new Specifications(Operator::EQ, Specifications::FORMAT_SINGLE, Specifications::TYPE_STRING),
            new Specifications(Operator::IN, Specifications::FORMAT_ARRAY, Specifications::TYPE_STRING),
            new Specifications(Operator::GT, Specifications::FORMAT_SINGLE, Specifications::TYPE_STRING),
            new Specifications(Operator::GTE, Specifications::FORMAT_SINGLE, Specifications::TYPE_STRING),
            new Specifications(Operator::LT, Specifications::FORMAT_SINGLE, Specifications::TYPE_STRING),
            new Specifications(Operator::LTE, Specifications::FORMAT_SINGLE, Specifications::TYPE_STRING),
            new Specifications(Operator::LIKE, Specifications::FORMAT_SINGLE, Specifications::TYPE_STRING),
            new Specifications(Operator::BETWEEN, Specifications::FORMAT_ARRAY, Specifications::TYPE_STRING, 2),
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
