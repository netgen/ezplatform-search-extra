<?php

namespace Netgen\EzPlatformSearchExtra\Tests\SetupFactory;

use EzSystems\EzPlatformSolrSearchEngine\Tests\SetupFactory\LegacySetupFactory as CoreSolrSetupFactory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Solr extends CoreSolrSetupFactory
{
    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    protected function externalBuildContainer(ContainerBuilder $containerBuilder)
    {
        $loader = new YamlFileLoader(
            $containerBuilder,
            new FileLocator(__DIR__ . '/../../../lib/Resources/config/')
        );

        $loader->load('search/solr.yml');
        $loader->load('persistence.yml');

        parent::externalBuildContainer($containerBuilder);
    }
}
