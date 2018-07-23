<?php

namespace Netgen\EzPlatformSearchExtra\Container\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;

/**
 * This compiler pass will add 'netgen.search.solr.criterion_visitor.subdocument_query' tag to the
 * selected eZ Systems provided criterion visitors.
 */
final class TagSubdocumentCriterionVisitorsPass implements CompilerPassInterface
{
    private static $subdocumentCriterionVisitorTag = 'netgen.search.solr.query.content.criterion_visitor.subdocument_query';
    private static $criterionVisitorIds = [
        'ezpublish.search.solr.query.common.criterion_visitor.logical_and',
        'ezpublish.search.solr.query.common.criterion_visitor.logical_not',
        'ezpublish.search.solr.query.common.criterion_visitor.logical_or',
        'ezpublish.search.solr.query.common.criterion_visitor.custom_field_in',
        'ezpublish.search.solr.query.common.criterion_visitor.custom_field_range',
    ];

    public function process(ContainerBuilder $container)
    {
        foreach (static::$criterionVisitorIds as $id) {
            if (!$container->hasDefinition($id)) {
                continue;
            }

            $definition = $container->getDefinition($id);
            $definition->addTag(static::$subdocumentCriterionVisitorTag);
        }
    }
}
