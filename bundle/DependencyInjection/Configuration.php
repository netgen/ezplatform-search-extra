<?php

namespace Netgen\Bundle\EzPlatformSearchExtraBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @var string
     */
    protected $rootNodeName;

    /**
     * @param string $rootNodeName
     */
    public function __construct($rootNodeName)
    {
        $this->rootNodeName = $rootNodeName;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root($this->rootNodeName);

        $this->addIndexableFieldTypeSection($rootNode);
        $this->addSearchResultExtractorSection($rootNode);

        return $treeBuilder;
    }

    private function addIndexableFieldTypeSection(ArrayNodeDefinition $nodeDefinition)
    {
        $nodeDefinition
            ->children()
                ->arrayNode('indexable_field_type')
                    ->info('Configure override for field type Indexable interface implementation')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('ezxmltext')
                            ->addDefaultsIfNotSet()
                            ->canBeDisabled()
                            ->children()
                                ->integerNode('short_text_limit')
                                    ->info("Maximum number of characters for the indexed short text ('value' string type field)")
                                    ->defaultValue(256)
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('ezrichtext')
                            ->addDefaultsIfNotSet()
                            ->canBeDisabled()
                            ->children()
                                ->integerNode('short_text_limit')
                                    ->info("Maximum number of characters for the indexed short text ('value' string type field)")
                                    ->defaultValue(256)
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addSearchResultExtractorSection(ArrayNodeDefinition $nodeDefinition)
    {
        $nodeDefinition
            ->children()
                ->booleanNode('use_native_search_result_extractor')
                    ->info('Get search result objects by reconstructing them from the returned Solr data, instead of loading them from the persistence layer')
                    ->defaultFalse()
                ->end()
            ->end();
    }
}
