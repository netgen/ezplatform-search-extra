<?php

namespace Netgen\Bundle\EzPlatformSearchExtraSolrBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        /** @noinspection PhpUnusedLocalVariableInspection */
        $rootNode = $treeBuilder->root('netgen_ez_platform_search_extra');

        return $treeBuilder;
    }
}
