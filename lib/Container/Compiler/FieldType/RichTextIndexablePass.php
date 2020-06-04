<?php

namespace Netgen\EzPlatformSearchExtra\Container\Compiler\FieldType;

use EzSystems\EzPlatformRichText\eZ\FieldType\RichText\SearchField;
use Netgen\EzPlatformSearchExtra\Core\FieldType\RichText\Indexable as IndexableRichText;
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
        $definition = $container->findDefinition(SearchField::class);

        $definition->setClass(IndexableRichText::class);
        $definition->setArgument(0, $shortTextLimit);
        $definition->addTag('ezplatform.field_type.indexable', ['alias' => 'ezrichtext']);

        $container->setDefinition(SearchField::class, $definition);
    }
}
