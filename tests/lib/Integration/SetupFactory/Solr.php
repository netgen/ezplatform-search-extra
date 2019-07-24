<?php

namespace Netgen\EzPlatformSearchExtra\Tests\Integration\SetupFactory;

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
        parent::externalBuildContainer($containerBuilder);

        $configPath = __DIR__ . '/../../../../lib/Resources/config/';
        $loader = new YamlFileLoader($containerBuilder, new FileLocator($configPath));
        $loader->load('search/common.yml');
        $loader->load('search/solr.yml');

        $testConfigPath = __DIR__ . '/../Resources/config/';
        $loader = new YamlFileLoader($containerBuilder, new FileLocator($testConfigPath));
        $loader->load('services.yml');

        // Needs to be added first because other passes depend on it
        $containerBuilder->addCompilerPass(new Compiler\TagSubdocumentCriterionVisitorsPass());
        $containerBuilder->addCompilerPass(new Compiler\AggregateContentSubdocumentMapperPass());
        $containerBuilder->addCompilerPass(new Compiler\AggregateContentTranslationSubdocumentMapperPass());
        $containerBuilder->addCompilerPass(new Compiler\AggregateSubdocumentQueryCriterionVisitorPass());
        $containerBuilder->addCompilerPass(new Compiler\RawFacetBuilderDomainVisitorPass());
        $containerBuilder->addCompilerPass(new Compiler\FieldTypeRegistryPass());
    }
}
