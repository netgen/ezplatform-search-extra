<?php

namespace Netgen\EzPlatformSearchExtra\Container\Compiler\FieldType;

use Netgen\EzPlatformSearchExtra\Core\FieldType\XmlText\Indexable as IndexableXmlText;
use RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RichTextIndexablePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $configurationParameterName = 'netgen_ez_platform_search_extra_configuration';

        if (!$container->hasParameter($configurationParameterName)) {
            return;
        }

        $configuration = $container->getParameter($configurationParameterName);

        if (!isset($configuration['indexable_field_type']['ezrichtext'])) {
            return;
        }

        $configuration = $configuration['indexable_field_type']['ezrichtext'];

        if ($configuration['override'] === true) {
            $this->redefineIndexableImplementation($container, $configuration);
        }
    }

    private function redefineIndexableImplementation(ContainerBuilder $container, array $configuration)
    {
        try {
            $originalServiceId = $this->getOriginalServiceId($container);
        } catch (RuntimeException $e) {
            return;
        }

        $definition = $container->findDefinition($originalServiceId);

        $definition->setClass(IndexableXmlText::class);
        $definition->setArgument(0, $configuration['short_text_limit']);
        $definition->addTag('ezpublish.fieldType.indexable', ['alias' => 'ezrichtext']);

        $container->setDefinition($originalServiceId, $definition);
    }

    private function getOriginalServiceId(ContainerBuilder $container)
    {
        $newServiceId = 'EzSystems\EzPlatformRichText\eZ\FieldType\RichText\SearchField';
        $oldServiceId = 'ezpublish.fieldType.indexable.ezrichtext';

        if ($container->hasDefinition($newServiceId)) {
            return $newServiceId;
        }

        if ($container->hasDefinition($oldServiceId)) {
            return $oldServiceId;
        }

        throw new RuntimeException('Could not find Indexable service');
    }
}
