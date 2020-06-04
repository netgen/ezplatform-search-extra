<?php

namespace Netgen\EzPlatformSearchExtra\Container\Compiler;

use eZ\Publish\Core\Persistence\FieldType as eZFieldType;
use Netgen\EzPlatformSearchExtra\Core\Persistence\FieldTypeRegistry;
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

        $fieldTypeRegistry = $container->findDefinition('ezpublish.persistence.field_type_registry');
        $fieldTypeRegistry->setClass(FieldTypeRegistry::class);
    }
}
