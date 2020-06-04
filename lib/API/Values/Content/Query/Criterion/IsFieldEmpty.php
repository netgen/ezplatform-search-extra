<?php

namespace Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications;
use InvalidArgumentException;

/**
 * IsFieldEmpty criterion matches Content field based on if its value is empty or not.
 */
class IsFieldEmpty extends Criterion
{
    /**
     * Indicates that the field value should be empty.
     *
     * @var int
     */
    const IS_EMPTY = 0;

    /**
     * Indicates that the field value shouldn't be empty.
     *
     * @var int
     */
    const IS_NOT_EMPTY = 1;

    /**
     * @param string $fieldDefinitionIdentifier
     * @param int $value Field value constant, one of self::IS_EMPTY and self::IS_NOT_EMPTY
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($fieldDefinitionIdentifier, $value)
    {
        if ($value !== self::IS_EMPTY && $value !== self::IS_NOT_EMPTY) {
            throw new InvalidArgumentException(
                "Invalid has field content value {$value}"
            );
        }

        parent::__construct($fieldDefinitionIdentifier, null, $value);
    }

    public function getSpecifications(): array
    {
        return [
            new Specifications(
                Operator::EQ,
                Specifications::FORMAT_SINGLE,
                Specifications::TYPE_INTEGER
            ),
        ];
    }
}
