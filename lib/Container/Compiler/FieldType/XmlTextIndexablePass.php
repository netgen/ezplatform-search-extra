<?php

namespace Netgen\EzPlatformSearchExtra\Container\Compiler\FieldType;

use Netgen\EzPlatformSearchExtra\Core\FieldType\XmlText\Indexable as IndexableXmlText;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class XmlTextIndexablePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $enabled = $container->getParameter('netgen_ez_platform_search_extra.indexable_field_type.ezxmltext.enabled');
        $shortTextLimit = $container->getParameter('netgen_ez_platform_search_extra.indexable_field_type.ezxmltext.short_text_limit');

        if ($enabled === true) {
            $this->redefineIndexableImplementation($container, $shortTextLimit);
        }
    }

    private function redefineIndexableImplementation(ContainerBuilder $container, $shortTextLimit)
    {
        $originalServiceId = 'ezpublish.fieldType.indexable.ezxmltext';

        if (!$container->has($originalServiceId)) {
            return;
        }

        $definition = $container->findDefinition($originalServiceId);

        $definition->setClass(IndexableXmlText::class);
        $definition->setArgument(0, $shortTextLimit);
        $definition->addTag('ezpublish.fieldType.indexable', ['alias' => 'ezxmltext']);

        $container->setDefinition($originalServiceId, $definition);
    }
}
