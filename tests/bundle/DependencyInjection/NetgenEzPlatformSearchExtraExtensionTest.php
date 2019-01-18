<?php

namespace Netgen\EzPlatformSearchExtraBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Netgen\Bundle\EzPlatformSearchExtraBundle\DependencyInjection\NetgenEzPlatformSearchExtraExtension;
use Netgen\EzPlatformSearchExtra\Core\FieldType\RichText\Indexable as RichTextIndexable;
use Netgen\EzPlatformSearchExtra\Core\FieldType\XmlText\Indexable as XmlTextIndexable;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class NetgenEzPlatformSearchExtraExtensionTest extends AbstractExtensionTestCase
{
    /**
     * @throws \Exception
     */
    protected function setUp()
    {
        parent::setUp();

        $loader = new YamlFileLoader(
            $this->container,
            new FileLocator(__DIR__ . '/_fixtures')
        );

        $loader->load('indexable_field_types.yml');

        $this->setParameter('kernel.bundles', []);
    }

    protected function getContainerExtensions()
    {
        return [
            new NetgenEzPlatformSearchExtraExtension(),
        ];
    }

    public function providerForIndexableFieldTypeDefaultConfiguration()
    {
        return [
            [
                [],
            ],
            [
                [
                    'indexable_field_type' => [],
                ],
            ],
            [
                [
                    'indexable_field_type' => [
                        'ezxmltext' => [],
                    ],
                ],
            ],
            [
                [
                    'indexable_field_type' => [
                        'ezxmltext' => [
                            'enabled' => true,
                            'short_text_limit' => 256
                        ],
                    ],
                ],
            ],
            [
                [
                    'indexable_field_type' => [
                        'ezrichtext' => [],
                    ],
                ],
            ],
            [
                [
                    'indexable_field_type' => [
                        'ezrichtext' => [
                            'enabled' => true,
                            'short_text_limit' => 256
                        ],
                    ],
                ],
            ],
            [
                [
                    'indexable_field_type' => [
                        'ezrichtext' => [],
                        'ezxmltext' => [],
                    ],
                ],
            ],
            [
                [
                    'indexable_field_type' => [
                        'ezxmltext' => [
                            'enabled' => true,
                            'short_text_limit' => 256
                        ],
                        'ezrichtext' => [
                            'enabled' => true,
                            'short_text_limit' => 256
                        ],
                    ],
                ],
            ],
            [
                [
                    'use_loading_search_result_extractor' => true,
                    'indexable_field_type' => [
                        'ezxmltext' => [
                            'enabled' => true,
                            'short_text_limit' => 256
                        ],
                        'ezrichtext' => [
                            'enabled' => true,
                            'short_text_limit' => 256
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider providerForIndexableFieldTypeDefaultConfiguration
     *
     * @param array $configuration
     */
    public function testIndexableFieldTypeDefaultConfiguration(array $configuration)
    {
        $this->load($configuration);

        $this->assertContainerBuilderHasParameter(
            'netgen_ez_platform_search_extra.use_loading_search_result_extractor',
            true
        );
        $this->assertContainerBuilderHasParameter(
            'netgen_ez_platform_search_extra.indexable_field_type.ezrichtext.enabled',
            true
        );
        $this->assertContainerBuilderHasParameter(
            'netgen_ez_platform_search_extra.indexable_field_type.ezrichtext.short_text_limit',
            256
        );
        $this->assertContainerBuilderHasParameter(
            'netgen_ez_platform_search_extra.indexable_field_type.ezxmltext.enabled',
            true
        );
        $this->assertContainerBuilderHasParameter(
            'netgen_ez_platform_search_extra.indexable_field_type.ezxmltext.short_text_limit',
            256
        );
    }
}
