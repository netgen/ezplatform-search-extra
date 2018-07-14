<?php

namespace Netgen\EzPlatformSearchExtra\Container\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;

/**
 * This compiler pass will register Content translation subdocument mappers.
 *
 * @see \Netgen\EzPlatformSearchExtra\Core\Search\Solr\SubdocumentMapper\ContentTranslationSubdocumentMapper
 * @see \Netgen\EzPlatformSearchExtra\Core\Search\Solr\SubdocumentMapper\ContentTranslationSubdocumentMapper\Aggregate
 */
final class AggregateContentTranslationSubdocumentMapperPass implements CompilerPassInterface
{
    private static $aggregateMapperId = 'netgen.search.solr.subdocument_mapper.content_translation.aggregate';
    private static $mapperTag = 'netgen.search.solr.subdocument_mapper.content_translation';

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(static::$aggregateMapperId)) {
            return;
        }

        $aggregateDefinition = $container->getDefinition(static::$aggregateMapperId);
        $mapperIds = $container->findTaggedServiceIds(static::$mapperTag);

        $this->registerMappers($aggregateDefinition, $mapperIds);
    }

    private function registerMappers(Definition $definition, array $mapperIds)
    {
        foreach (array_keys($mapperIds) as $id) {
            $definition->addMethodCall('addMapper', [new Reference($id)]);
        }
    }
}
