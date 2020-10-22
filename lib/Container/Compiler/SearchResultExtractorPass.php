<?php

declare(strict_types=1);

namespace Netgen\EzPlatformSearchExtra\Container\Compiler;

use Netgen\EzPlatformSearchExtra\Core\Search\Solr\ResultExtractor\NativeResultExtractor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Configures native search result extractor if the loading search result extractor is disabled.
 *
 * @see \Netgen\EzPlatformSearchExtra\Core\Search\Solr\ResultExtractor\NativeResultExtractor
 */
final class SearchResultExtractorPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function process(ContainerBuilder $container): void
    {
        $useLoadingSearchResultExtractor = $container->getParameter(
            'netgen_ez_platform_search_extra.use_loading_search_result_extractor'
        );

        if ($useLoadingSearchResultExtractor === true) {
            return;
        }

        $serviceId = 'netgen.search.solr.result_extractor.content.native_override';
        $decoratedServiceId = 'ezpublish.search.solr.result_extractor.content.native';

        $container
            ->register($serviceId, NativeResultExtractor::class)
            ->setDecoratedService($decoratedServiceId)
            ->setArguments([
                new Reference($serviceId . '.inner'),
                new Reference('ezpublish.search.solr.query.content.facet_builder_visitor.aggregate'),
                new Reference('ezpublish.search.solr.query.content.aggregation_result_extractor.dispatcher'),
                new Reference('ezpublish.search.solr.gateway.endpoint_registry'),
            ]);

        $serviceId = 'netgen.search.solr.result_extractor.location.native_override';
        $decoratedServiceId = 'ezpublish.search.solr.result_extractor.location.native';

        $container
            ->register($serviceId, NativeResultExtractor::class)
            ->setDecoratedService($decoratedServiceId)
            ->setArguments([
                new Reference($serviceId . '.inner'),
                new Reference('ezpublish.search.solr.query.location.facet_builder_visitor.aggregate'),
                new Reference('ezpublish.search.solr.query.location.aggregation_result_extractor.dispatcher'),
                new Reference('ezpublish.search.solr.gateway.endpoint_registry'),
            ]);
    }
}
