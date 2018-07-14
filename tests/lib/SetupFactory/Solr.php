<?php

namespace Netgen\EzPlatformSearchExtra\Tests\SetupFactory;

use EzSystems\EzPlatformSolrSearchEngine\Tests\SetupFactory\LegacySetupFactory as CoreSolrSetupFactory;
use Netgen\EzPlatformSearchExtra\Container\Compiler;
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
        $configPath = __DIR__ . '/../../../lib/Resources/config/';
        $loader = new YamlFileLoader($containerBuilder, new FileLocator($configPath));
        $loader->load('search/common.yml');
        $loader->load('search/solr.yml');
        $loader->load('persistence.yml');

        $testConfigPath = __DIR__ . '/../Resources/config/';
        $loader = new YamlFileLoader($containerBuilder, new FileLocator($testConfigPath));
        $loader->load('services.yml');

        $containerBuilder->addCompilerPass(new Compiler\TagSubdocumentCriterionVisitorsPass());
        $containerBuilder->addCompilerPass(new Compiler\AggregateContentSubdocumentMapperPass());
        $containerBuilder->addCompilerPass(new Compiler\AggregateContentTranslationSubdocumentMapperPass());
        $containerBuilder->addCompilerPass(new Compiler\AggregateSubdocumentQueryCriterionVisitorPass());

        parent::externalBuildContainer($containerBuilder);
    }
}
