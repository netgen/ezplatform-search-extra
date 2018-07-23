<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Common\FieldValueMapper;

use eZ\Publish\Core\Search\Common\FieldValueMapper;
use eZ\Publish\SPI\Search\FieldType\IdentifierField;
use eZ\Publish\SPI\Search\Field;

/**
 * Common identifier field value mapper implementation, performing different pattern
 * replacement than the original.
 *
 * @see \eZ\Publish\Core\Search\Common\FieldValueMapper\IdentifierMapper
 */
final class IdentifierMapper extends FieldValueMapper
{
    public function canMap(Field $field)
    {
        return $field->type instanceof IdentifierField;
    }

    public function map(Field $field)
    {
        return $this->convert($field->value);
    }

    protected function convert($value)
    {
        // Remove everything except alphanumeric characters, slash (/), underscore (_) and minus (-)
        return preg_replace('([^A-Za-z0-9_\-/]+)', '', $value);
    }
}
