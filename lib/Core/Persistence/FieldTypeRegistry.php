<?php

namespace Netgen\EzPlatformSearchExtra\Core\Persistence;

use eZ\Publish\Core\Persistence\FieldTypeRegistry as CorePersistenceFieldTypeRegistry;
use eZ\Publish\SPI\Persistence\FieldType as FieldTypeInterface;

final class FieldTypeRegistry extends CorePersistenceFieldTypeRegistry
{
    public function getFieldType(string $identifier): FieldTypeInterface
    {
        if (!isset($this->fieldTypes[$identifier])) {
            $this->fieldTypes[$identifier] = new FieldType(
                $this->getCoreFieldType($identifier)
            );
        }

        return $this->fieldTypes[$identifier];
    }
}
