<?php

namespace Netgen\Bundle\EzPlatformSearchExtraBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class NetgenEzPlatformSearchExtraExtension extends Extension
{
    /**
     * @param array $configs
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $this->processConfiguration($configuration, $configs);
        $activatedBundlesMap = $container->getParameter('kernel.bundles');

        $loader = new Loader\YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../lib/Resources/config/search/')
        );

        if (array_key_exists('EzPublishLegacySearchEngineBundle', $activatedBundlesMap)) {
            $loader->load('legacy.yml');
        }

        if (array_key_exists('EzSystemsEzPlatformSolrSearchEngineBundle', $activatedBundlesMap)) {
            $loader->load('solr.yml');
        }
    }
}
