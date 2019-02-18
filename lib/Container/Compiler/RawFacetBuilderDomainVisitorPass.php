<?php

namespace Netgen\EzPlatformSearchExtra\Container\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;

/**
 * This compiler pass will register RawFacetBuilder Domain visitors.
 *
 * @see \Netgen\EzPlatformSearchExtra\Core\Search\Solr\API\FacetBuilder\RawFacetBuilder\Domain
 * @see \Netgen\EzPlatformSearchExtra\Core\Search\Solr\Query\Common\FacetBuilderVisitor\RawFacetBuilderVisitor\DomainVisitor\Aggregate
 */
final class RawFacetBuilderDomainVisitorPass implements CompilerPassInterface
{
    private static $aggregateVisitorId = 'netgen.search.solr.query.common.facet_builder_visitor.raw.domain_visitor.aggregate';
    private static $visitorTag = 'netgen.search.solr.query.common.facet_builder_visitor.raw.domain_visitor';

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
