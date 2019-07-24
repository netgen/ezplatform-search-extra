<?php

namespace Netgen\EzPlatformSearchExtra\Core\Persistence;

use eZ\Publish\Core\Persistence\FieldTypeRegistry as CorePersistenceFieldTypeRegistry;

final class LegacyFieldTypeRegistry extends CorePersistenceFieldTypeRegistry
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
