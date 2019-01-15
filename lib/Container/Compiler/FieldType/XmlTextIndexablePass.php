<?php

namespace Netgen\EzPlatformSearchExtra\Container\Compiler\FieldType;

use Netgen\EzPlatformSearchExtra\Core\FieldType\XmlText\Indexable as IndexableXmlText;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class XmlTextIndexablePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $configurationParameterName = 'netgen_ez_platform_search_extra_configuration';

        if (!$container->hasParameter($configurationParameterName)) {
            return;
        }

        $configuration = $container->getParameter($configurationParameterName);

        if (!isset($configuration['indexable_field_type']['ezxmltext'])) {
            return;
        }

        $configuration = $configuration['indexable_field_type']['ezxmltext'];

        if ($configuration['override'] === true) {
            $this->redefineIndexableImplementation($container, $configuration);
        }
    }

    private function redefineIndexableImplementation(ContainerBuilder $container, array $configuration)
    {
        $originalServiceId = 'ezpublish.fieldType.indexable.ezxmltext';

        if (!$container->hasDefinition($originalServiceId)) {
            return;
        }

        $definition = $container->findDefinition($originalServiceId);

        $definition->setClass(IndexableXmlText::class);
        $definition->setArgument(0, $configuration['short_text_limit']);
        $definition->addTag('ezpublish.fieldType.indexable', ['alias' => 'ezxmltext']);

        $container->setDefinition($originalServiceId, $definition);
    }
}
