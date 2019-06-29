<?php

namespace Netgen\EzPlatformSearchExtra\Tests\Integration\API;

use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\ContentId as ContentIdSortClause;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\FacetBuilder\CustomFieldFacetBuilder;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Search\Facet\CustomFieldFacet;

class CustomFieldFacetTest extends BaseTest
{
    public function providerForTestFind()
    {
        return [
            [
                new LocationQuery([
                    'facetBuilders' => [
                        new CustomFieldFacetBuilder([
                            'fieldName' => 'forest_tree_value_s',
                            'limit' => 10,
                            'minCount' => 1,
                            'name' => 'test_facet',
                            'sort' => CustomFieldFacetBuilder::COUNT_DESC,
                        ]),
                    ],
                    'filter' => new ContentTypeIdentifier('forest'),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [
                    new CustomFieldFacet([
                        'entries' => [
                            'hrast' => 4,
                            'lipa' => 3,
                            'grab' => 2,
                            'jasen' => 2,
                            'jela' => 1,
                            'smreka' => 1,
                        ],
                        'name' => 'test_facet',
                    ]),
                ],
            ],
            [
                new LocationQuery([
                    'facetBuilders' => [
                        new CustomFieldFacetBuilder([
                            'fieldName' => 'forest_tree_value_s',
                            'limit' => 10,
                            'minCount' => 1,
                            'name' => 'test_facet',
                            'sort' => CustomFieldFacetBuilder::TERM_ASC,
                        ]),
                    ],
                    'filter' => new ContentTypeIdentifier('forest'),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [
                    new CustomFieldFacet([
                        'entries' => [
                            'grab' => 2,
                            'hrast' => 4,
                            'jasen' => 2,
                            'jela' => 1,
                            'lipa' => 3,
                            'smreka' => 1,
                        ],
                        'name' => 'test_facet',
                    ]),
                ],
            ],
            [
                new LocationQuery([
                    'facetBuilders' => [
                        new CustomFieldFacetBuilder([
                            'fieldName' => 'forest_tree_value_s',
                            'limit' => 4,
                            'minCount' => 1,
                            'name' => 'test_facet',
                            'sort' => CustomFieldFacetBuilder::COUNT_DESC,
                        ]),
                    ],
                    'filter' => new ContentTypeIdentifier('forest'),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [
                    new CustomFieldFacet([
                        'entries' => [
                            'hrast' => 4,
                            'lipa' => 3,
                            'grab' => 2,
                            'jasen' => 2,
                        ],
                        'name' => 'test_facet',
                    ]),
                ],
            ],
            [
                new LocationQuery([
                    'facetBuilders' => [
                        new CustomFieldFacetBuilder([
                            'fieldName' => 'forest_tree_value_s',
                            'limit' => 5,
                            'minCount' => 1,
                            'name' => 'test_facet',
                            'sort' => CustomFieldFacetBuilder::TERM_ASC,
                        ]),
                    ],
                    'filter' => new ContentTypeIdentifier('forest'),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [
                    new CustomFieldFacet([
                        'entries' => [
                            'grab' => 2,
                            'hrast' => 4,
                            'jasen' => 2,
                            'jela' => 1,
                            'lipa' => 3,
                        ],
                        'name' => 'test_facet',
                    ]),
                ],
            ],
            [
                new LocationQuery([
                    'facetBuilders' => [
                        new CustomFieldFacetBuilder([
                            'fieldName' => 'forest_tree_value_s',
                            'limit' => 10,
                            'minCount' => 3,
                            'name' => 'test_facet',
                            'sort' => CustomFieldFacetBuilder::COUNT_DESC,
                        ]),
                    ],
                    'filter' => new ContentTypeIdentifier('forest'),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [
                    new CustomFieldFacet([
                        'entries' => [
                            'hrast' => 4,
                            'lipa' => 3,
                        ],
                        'name' => 'test_facet',
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
        $contentTypeCreateStruct = $contentTypeService->newContentTypeCreateStruct('forest');
        $contentTypeCreateStruct->mainLanguageCode = 'eng-GB';
        $contentTypeCreateStruct->names = ['eng-GB' => 'Forest type'];
        $fieldDefinitionCreateStruct = $contentTypeService->newFieldDefinitionCreateStruct('tree', 'ezstring');
        $contentTypeCreateStruct->addFieldDefinition($fieldDefinitionCreateStruct);
        $contentTypeDraft = $contentTypeService->createContentType($contentTypeCreateStruct, [reset($contentTypeGroups)]);
        $contentTypeService->publishContentTypeDraft($contentTypeDraft);
        $contentType = $contentTypeService->loadContentTypeByIdentifier('forest');

        $values = [
            'hrast',
            'hrast',
            'hrast',
            'hrast',
            'lipa',
            'lipa',
            'lipa',
            'grab',
            'grab',
            'jasen',
            'jasen',
            'smreka',
            'jela',
        ];

        $locationCreateStruct = $locationService->newLocationCreateStruct(2);

        foreach ($values as $value) {
            $contentCreateStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
            $contentCreateStruct->setField('tree', $value);
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
}
