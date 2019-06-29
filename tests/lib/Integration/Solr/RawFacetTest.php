<?php

namespace Netgen\EzPlatformSearchExtra\Tests\Integration\Solr;

use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\FacetBuilder\CustomFieldFacetBuilder;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Search\Facet\CustomFieldFacet;
use eZ\Publish\API\Repository\Tests\BaseTest;
use Netgen\EzPlatformSearchExtra\Core\Search\Solr\API\Facet\RawFacet;
use Netgen\EzPlatformSearchExtra\Core\Search\Solr\API\FacetBuilder\RawFacetBuilder;

class RawFacetTest extends BaseTest
{
    public function providerForTestFind()
    {
        return [
            [
                new LocationQuery([
                    'facetBuilders' => [
                        new RawFacetBuilder([
                            'name' => 'test_facet',
                            'parameters' => [
                                'type' => 'terms',
                                'field' => 'toaster_price_value_i',
                                'allBuckets' => true,
                                'sort' => 'count desc',
                                'facet' => [
                                    'average' => 'avg(toaster_price_value_i)',
                                    'sum' => 'sum(toaster_price_value_i)',
                                ],
                            ],
                        ]),
                    ],
                    'filter' => new ContentTypeIdentifier('toaster'),
                ]),
                [
                    new RawFacet([
                        'name' => 'test_facet',
                        'data' => json_decode(
                            json_encode(
                                [
                                    'allBuckets' => [
                                        'count' => 10,
                                        'average' => 30.5,
                                        'sum' => 305,
                                    ],
                                    'buckets' => [
                                        [
                                            'val' => 55,
                                            'count' => 4,
                                            'average' => 55,
                                            'sum' => 220,
                                        ],
                                        [
                                            'val' => 20,
                                            'count' => 3,
                                            'average' => 20,
                                            'sum' => 60,
                                        ],
                                        [
                                            'val' => 10,
                                            'count' => 2,
                                            'average' => 10,
                                            'sum' => 20,
                                        ],
                                        [
                                            'val' => 5,
                                            'count' => 1,
                                            'average' => 5,
                                            'sum' => 5,
                                        ],
                                    ],
                                ]
                            )
                        ),
                    ]),
                ],
            ],
            [
                new LocationQuery([
                    'facetBuilders' => [
                        new CustomFieldFacetBuilder([
                            'fieldName' => 'toaster_price_value_i',
                            'limit' => 10,
                            'minCount' => 1,
                            'name' => 'test_facet',
                            'sort' => CustomFieldFacetBuilder::COUNT_DESC,
                        ]),
                        new RawFacetBuilder([
                            'name' => 'test_facet2',
                            'parameters' => [
                                'type' => 'terms',
                                'field' => 'toaster_price_value_i',
                                'allBuckets' => true,
                                'sort' => 'count desc',
                                'limit' => 0,
                                'facet' => [
                                    'average' => 'avg(toaster_price_value_i)',
                                    'sum' => 'sum(toaster_price_value_i)',
                                ],
                            ],
                        ]),
                    ],
                    'filter' => new ContentTypeIdentifier('toaster'),
                ]),
                [
                    new CustomFieldFacet([
                        'entries' => [
                            5 => 1,
                            10 => 2,
                            20 => 3,
                            55 => 4,
                        ],
                        'name' => 'test_facet',
                    ]),
                    new RawFacet([
                        'name' => 'test_facet2',
                        'data' => json_decode(
                            json_encode(
                                [
                                    'allBuckets' => [
                                        'count' => 10,
                                        'average' => 30.5,
                                        'sum' => 305,
                                    ],
                                    'buckets' => [],
                                ]
                            )
                        ),
                    ]),
                ],
            ],
            [
                new LocationQuery([
                    'facetBuilders' => [
                        new RawFacetBuilder([
                            'name' => 'test_facet',
                            'parameters' => [
                                'type' => 'terms',
                                'field' => 'toaster_price_value_i',
                                'allBuckets' => true,
                                'limit' => 0,
                                'facet' => [
                                    'average' => 'avg(toaster_price_value_i)',
                                    'sum' => 'sum(toaster_price_value_i)',
                                    'maximum' => 'max(toaster_price_value_i)',
                                    'minimum' => 'min(toaster_price_value_i)',
                                ],
                            ],
                        ]),
                    ],
                    'filter' => new ContentTypeIdentifier('toaster'),
                ]),
                [
                    new RawFacet([
                        'name' => 'test_facet',
                        'data' => json_decode(
                            json_encode(
                                [
                                    'allBuckets' => [
                                        'count' => 10,
                                        'average' => 30.5,
                                        'sum' => 305,
                                        'maximum' => 55,
                                        'minimum' => 5,
                                    ],
                                    'buckets' => [],
                                ]
                            )
                        ),
                    ]),
                ],
            ],
            [
                new LocationQuery([
                    'facetBuilders' => [
                        new RawFacetBuilder([
                            'name' => 'test_facet',
                            'parameters' => [
                                'type' => 'terms',
                                'field' => 'toaster_price_value_i',
                                'allBuckets' => true,
                                'limit' => 0,
                                'facet' => [
                                    'maximum' => 'max(toaster_price_value_i)',
                                ],
                            ],
                        ]),
                        new RawFacetBuilder([
                            'name' => 'test_facet',
                            'parameters' => [
                                'type' => 'terms',
                                'field' => 'toaster_price_value_i',
                                'allBuckets' => true,
                                'facet' => [
                                    'name' => [
                                        'type' => 'terms',
                                        'field' => 'content_name_s',
                                    ],
                                ],
                            ],
                        ]),
                    ],
                    'filter' => new ContentTypeIdentifier('toaster'),
                ]),
                [
                    new RawFacet([
                        'name' => 'test_facet',
                        'data' => json_decode(
                            json_encode(
                                [
                                    'allBuckets' => [
                                        'count' => 10,
                                        'maximum' => 55,
                                    ],
                                    'buckets' => [],
                                ]
                            )
                        ),
                    ]),
                    new RawFacet([
                        'name' => 'test_facet',
                        'data' => json_decode(
                            json_encode(
                                [
                                    'allBuckets' => [
                                        'count' => 10,
                                    ],
                                    'buckets' => [
                                        [
                                            'val' => 55,
                                            'count' => 4,
                                            'name' => [
                                                'buckets' => [
                                                    [
                                                        'val' => '55',
                                                        'count' => 4,
                                                    ]
                                                ],
                                            ],
                                        ],
                                        [
                                            'val' => 20,
                                            'count' => 3,
                                            'name' => [
                                                'buckets' => [
                                                    [
                                                        'val' => '20',
                                                        'count' => 3,
                                                    ]
                                                ],
                                            ],
                                        ],
                                        [
                                            'val' => 10,
                                            'count' => 2,
                                            'name' => [
                                                'buckets' => [
                                                    [
                                                        'val' => '10',
                                                        'count' => 2,
                                                    ]
                                                ],
                                            ],
                                        ],
                                        [
                                            'val' => 5,
                                            'count' => 1,
                                            'name' => [
                                                'buckets' => [
                                                    [
                                                        'val' => '5',
                                                        'count' => 1,
                                                    ]
                                                ],
                                            ],
                                        ],
                                    ],
                                ]
                            )
                        ),
                    ]),
                ],
            ],
        ];
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testPrepareTestFixtures()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();
        $locationService = $repository->getLocationService();

        $contentTypeGroups = $contentTypeService->loadContentTypeGroups();
        $contentTypeCreateStruct = $contentTypeService->newContentTypeCreateStruct('toaster');
        $contentTypeCreateStruct->mainLanguageCode = 'eng-GB';
        $contentTypeCreateStruct->names = ['eng-GB' => 'Toaster'];
        $fieldDefinitionCreateStruct = $contentTypeService->newFieldDefinitionCreateStruct('price', 'ezinteger');
        $contentTypeCreateStruct->addFieldDefinition($fieldDefinitionCreateStruct);
        $contentTypeDraft = $contentTypeService->createContentType($contentTypeCreateStruct, [reset($contentTypeGroups)]);
        $contentTypeService->publishContentTypeDraft($contentTypeDraft);
        $contentType = $contentTypeService->loadContentTypeByIdentifier('toaster');

        $values = [
            5,
            10,
            10,
            20,
            20,
            20,
            55,
            55,
            55,
            55,
        ];

        $locationCreateStruct = $locationService->newLocationCreateStruct(2);

        foreach ($values as $value) {
            $contentCreateStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
            $contentCreateStruct->setField('price', $value);
            $contentDraft = $contentService->createContent($contentCreateStruct, [$locationCreateStruct]);
            $contentService->publishVersion($contentDraft->versionInfo);
        }

        $this->refreshSearch($repository);

        $this->assertTrue(true);
    }

    /**
     * @dataProvider providerForTestFind
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     * @param \eZ\Publish\API\Repository\Values\Content\Search\Facet[] $expectedFacets
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testFindContent(Query $query, array $expectedFacets)
    {
        $searchService = $this->getSearchService(false);

        $searchResult = $searchService->findContentInfo($query);

        $this->assertEquals($expectedFacets, $searchResult->facets);
    }

    /**
     * @dataProvider providerForTestFind
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LocationQuery $query
     * @param \eZ\Publish\API\Repository\Values\Content\Search\Facet[] $expectedFacets
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testFindLocations(LocationQuery $query, $expectedFacets)
    {
        $searchService = $this->getSearchService(false);

        $searchResult = $searchService->findLocations($query);

        $this->assertEquals($expectedFacets, $searchResult->facets);
    }

    protected function getSearchService($initialInitializeFromScratch = true)
    {
        return $this->getRepository($initialInitializeFromScratch)->getSearchService();
    }
}
