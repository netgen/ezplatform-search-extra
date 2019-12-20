<?php

namespace Netgen\EzPlatformSearchExtra\Container\Compiler;

use eZ\Publish\Core\Persistence\FieldType as eZFieldType;
use eZ\Publish\SPI\FieldType\Generic\Type as GenericFieldType;
use Netgen\EzPlatformSearchExtra\Core\Persistence\FieldTypeRegistry;
use Netgen\EzPlatformSearchExtra\Core\Persistence\LegacyFieldTypeRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FieldTypeRegistryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('ezpublish.persistence.field_type_registry')) {
            return;
        }

        if (method_exists(eZFieldType::class, 'isEmptyValue')) {
            return;
        }

        $fieldTypeRegistryClass = LegacyFieldTypeRegistry::class;
        if (class_exists(GenericFieldType::class)) {
            // Generic field type only exists in eZ Platform v3
            $fieldTypeRegistryClass = FieldTypeRegistry::class;
        }

        $fieldTypeRegistry = $container->findDefinition('ezpublish.persistence.field_type_registry');
        $fieldTypeRegistry->setClass($fieldTypeRegistryClass);

        if (\count($fieldTypeRegistry->getArguments()) > 0) {
            $fieldTypeRegistry->setArgument(0, $fieldTypeRegistryClass);
        }
    }
}
