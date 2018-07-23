<?php

namespace Netgen\EzPlatformSearchExtra\Container\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;

/**
 * This compiler pass will register subdocument Criterion visitors.
 *
 * @see \EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor\Aggregate
 */
final class AggregateSubdocumentQueryCriterionVisitorPass implements CompilerPassInterface
{
    private static $aggregateVisitorId = 'netgen.search.solr.query.content.criterion_visitor.subdocument_query.aggregate';
    private static $visitorTag = 'netgen.search.solr.query.content.criterion_visitor.subdocument_query';

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(static::$aggregateVisitorId)) {
            return;
        }

        $aggregateDefinition = $container->getDefinition(static::$aggregateVisitorId);
        $mapperIds = $container->findTaggedServiceIds(static::$visitorTag);

        $this->registerMappers($aggregateDefinition, $mapperIds);
    }

    private function registerMappers(Definition $definition, array $visitorIds)
    {
        foreach (array_keys($visitorIds) as $id) {
            $definition->addMethodCall('addVisitor', [new Reference($id)]);
        }
    }
}
