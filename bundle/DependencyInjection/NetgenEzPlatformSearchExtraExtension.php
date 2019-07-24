<?php

namespace Netgen\Bundle\EzPlatformSearchExtraBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class NetgenEzPlatformSearchExtraExtension extends Extension
{
    public function getAlias()
    {
        return 'netgen_ez_platform_search_extra';
    }

    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration($this->getAlias());
    }

    /**
     * @param array $configs
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $activatedBundlesMap = $container->getParameter('kernel.bundles');

        $loader = new Loader\YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../lib/Resources/config/')
        );

        if (array_key_exists('EzPublishLegacySearchEngineBundle', $activatedBundlesMap)) {
            $loader->load('search/legacy.yml');
        }

        if (array_key_exists('EzSystemsEzPlatformSolrSearchEngineBundle', $activatedBundlesMap)) {
            $loader->load('search/solr.yml');
        }

        $loader->load('search/common.yml');

        $this->processExtensionConfiguration($configs, $container);
    }

    private function processExtensionConfiguration(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);

        if ($configuration === null) {
            return;
        }

        $configuration = $this->processConfiguration($configuration, $configs);

        $this->processIndexableFieldTypeConfiguration($configuration, $container);
        $this->processSearchResultExtractorConfiguration($configuration, $container);
    }

    private function processSearchResultExtractorConfiguration(array $configuration, ContainerBuilder $container)
    {
        $container->setParameter(
            'netgen_ez_platform_search_extra.use_loading_search_result_extractor',
            $configuration['use_loading_search_result_extractor']
        );
    }

    private function processIndexableFieldTypeConfiguration(array $configuration, ContainerBuilder $container)
    {
        $container->setParameter(
            'netgen_ez_platform_search_extra.indexable_field_type.ezrichtext.enabled',
            $configuration['indexable_field_type']['ezrichtext']['enabled']
        );
        $container->setParameter(
            'netgen_ez_platform_search_extra.indexable_field_type.ezrichtext.short_text_limit',
            $configuration['indexable_field_type']['ezrichtext']['short_text_limit']
        );
        $container->setParameter(
            'netgen_ez_platform_search_extra.indexable_field_type.ezxmltext.enabled',
            $configuration['indexable_field_type']['ezxmltext']['enabled']
        );
        $container->setParameter(
            'netgen_ez_platform_search_extra.indexable_field_type.ezxmltext.short_text_limit',
            $configuration['indexable_field_type']['ezxmltext']['short_text_limit']
        );
    }
}
