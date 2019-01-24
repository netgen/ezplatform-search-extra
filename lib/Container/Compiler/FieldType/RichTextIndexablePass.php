<?php

namespace Netgen\EzPlatformSearchExtra\Container\Compiler\FieldType;

use EzSystems\EzPlatformRichText\eZ\FieldType\RichText\SearchField;
use Netgen\EzPlatformSearchExtra\Core\FieldType\RichText\Indexable as IndexableRichText;
use RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RichTextIndexablePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $enabled = $container->getParameter('netgen_ez_platform_search_extra.indexable_field_type.ezrichtext.enabled');
        $shortTextLimit = $container->getParameter('netgen_ez_platform_search_extra.indexable_field_type.ezrichtext.short_text_limit');

        if ($enabled === true) {
            $this->redefineIndexableImplementation($container, $shortTextLimit);
        }
    }

    private function redefineIndexableImplementation(ContainerBuilder $container, $shortTextLimit)
    {
        try {
            $originalServiceId = $this->getOriginalServiceId($container);
        } catch (RuntimeException $e) {
            return;
        }

        $definition = $container->findDefinition($originalServiceId);

        $definition->setClass(IndexableRichText::class);
        $definition->setArgument(0, $shortTextLimit);
        $definition->addTag('ezpublish.fieldType.indexable', ['alias' => 'ezrichtext']);

        $container->setDefinition($originalServiceId, $definition);
    }

    private function getOriginalServiceId(ContainerBuilder $container)
    {
        $newServiceId = SearchField::class;
        $oldServiceId = 'ezpublish.fieldType.indexable.ezrichtext';

        if ($container->has($newServiceId)) {
            return $newServiceId;
        }

        if ($container->has($oldServiceId)) {
            return $oldServiceId;
        }

        throw new RuntimeException('Could not find Indexable service');
    }
}
