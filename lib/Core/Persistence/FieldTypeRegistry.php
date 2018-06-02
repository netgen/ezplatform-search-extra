<?php

namespace Netgen\EzPlatformSearchExtra\Core\Persistence;

use eZ\Publish\Core\Persistence\FieldTypeRegistry as CorePersistenceFieldTypeRegistry;

class FieldTypeRegistry extends CorePersistenceFieldTypeRegistry
{
    public function getFieldType($identifier)
    {
        if (!isset($this->fieldTypeMap[$identifier])) {
            $this->fieldTypeMap[$identifier] = new FieldType(
                $this->getCoreFieldType($identifier)
            );
        }

        return $this->fieldTypeMap[$identifier];
    }
}
