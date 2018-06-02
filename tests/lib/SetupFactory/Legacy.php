<?php

namespace Netgen\EzPlatformSearchExtra\Tests\SetupFactory;

use eZ\Publish\API\Repository\Tests\SetupFactory\Legacy as CoreLegacySetupFactory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Legacy extends CoreLegacySetupFactory
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
            new FileLocator(__DIR__ . '/../../../lib/Resources/config/search/')
        );

        $loader->load('legacy.yml');
    }
}
