<?php

namespace Netgen\EzPlatformSearchExtra\Tests\Integration\API;

use eZ\Publish\API\Repository\Tests\BaseTest;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Search\WordSuggestion;
use Netgen\EzPlatformSearchExtra\Tests\API\FullTextCriterion;

/**
 * @group fulltext-spellcheck
 */
class FulltextSpellcheckCriterionTest extends BaseTest
{
    public function providerForTestFind()
    {
        return [
            [
                new LocationQuery([
                    'query' => new FullTextCriterion('sucess'),
                    'filter' => new Criterion\ContentTypeIdentifier('spellcheck_test'),
                ]),
                [
                    [
                        new WordSuggestion([
                            'originalWord' => 'sucess',
                            'suggestedWord' => 'success',
                            'frequency' => 6,
                        ]),
                    ],
                ],
            ],
            [
                new LocationQuery([
                    'query' => new FullTextCriterion('sucessful'),
                    'filter' => new Criterion\ContentTypeIdentifier('spellcheck_test'),
                ]),
                [
                    [
                        new WordSuggestion([
                            'originalWord' => 'sucessful',
                            'suggestedWord' => 'successful',
                            'frequency' => 2,
                        ]),
                        new WordSuggestion([
                            'originalWord' => 'sucessful',
                            'suggestedWord' => 'successfully',
                            'frequency' => 4,
                        ]),
                    ],
                ],
            ],
            [
                new LocationQuery([
                    'query' => new FullTextCriterion('success'),
                    'filter' => new Criterion\ContentTypeIdentifier('spellcheck_test'),
                ]),
                [
                    [
                        new WordSuggestion([
                            'originalWord' => 'success',
                            'suggestedWord' => 'successful',
                            'frequency' => 2,
                        ])
                    ]
                ],
            ],
            [
                new LocationQuery([
                    'query' => new FullTextCriterion('medioccre'),
                    'filter' => new Criterion\ContentTypeIdentifier('spellcheck_test'),
                ]),
                [
                    [
                        new WordSuggestion([
                            'originalWord' => 'medioccre',
                            'suggestedWord' => 'mediocre',
                            'frequency' => 2,
                        ])
                    ]
                ],
            ],
            [
                new LocationQuery([
                    'query' => new FullTextCriterion('mediocre'),
                    'filter' => new Criterion\ContentTypeIdentifier('spellcheck_test'),
                ]),
                [],
            ],
            [
                new LocationQuery([
                    'query' => new FullTextCriterion('asdfghjk'),
                    'filter' => new Criterion\ContentTypeIdentifier('spellcheck_test'),
                ]),
                [],
            ],
        ];
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testPrepareTestFixtures()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();
        $locationService = $repository->getLocationService();

        $contentTypeGroups = $contentTypeService->loadContentTypeGroups();
        $contentTypeCreateStruct = $contentTypeService->newContentTypeCreateStruct('spellcheck_test');
        $contentTypeCreateStruct->mainLanguageCode = 'eng-GB';
        $contentTypeCreateStruct->names = ['eng-GB' => 'Article'];
        $fieldDefinitionCreateStruct = $contentTypeService->newFieldDefinitionCreateStruct('name', 'ezstring');
        $fieldDefinitionCreateStruct->position = 0;
        $contentTypeCreateStruct->addFieldDefinition($fieldDefinitionCreateStruct);
        $fieldDefinitionCreateStruct = $contentTypeService->newFieldDefinitionCreateStruct('description', 'ezstring');
        $fieldDefinitionCreateStruct->position = 1;
        $contentTypeCreateStruct->addFieldDefinition($fieldDefinitionCreateStruct);
        $contentTypeDraft = $contentTypeService->createContentType($contentTypeCreateStruct, [reset($contentTypeGroups)]);
        $contentTypeService->publishContentTypeDraft($contentTypeDraft);
        $contentType = $contentTypeService->loadContentTypeByIdentifier('spellcheck_test');

        $values = [
            'Test content 1' => 'This content has been published successfully',
            'Test content 2' => 'This is a success',
            'Test content 3' => 'This is a successful content',
            'Test content 4' => 'This content needs a success',
            'Test content 5' => 'Testing if success word is spelled successfully',
            'Test content 6' => 'This content is mediocre',
        ];

        $locationCreateStruct = $locationService->newLocationCreateStruct(2);

        foreach ($values as $title => $description) {
            $contentCreateStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
            $contentCreateStruct->setField('name', $title);
            $contentCreateStruct->setField('description', $description);
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
     * @param \Netgen\EzPlatformSearchExtra\API\Values\Content\Search\WordSuggestion[] $expectedWordSuggestions
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testFindContent(Query $query, array $expectedWordSuggestions)
    {
        $searchService = $this->getSearchService(false);

        /** @var \Netgen\EzPlatformSearchExtra\API\Values\Content\Search\SearchResult $searchResult */
        $searchResult = $searchService->findContentInfo($query);

        $this->assertEquals($expectedWordSuggestions, $searchResult->suggestion->getSuggestions());
    }

    /**
     * @dataProvider providerForTestFind
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LocationQuery $query
     * @param \Netgen\EzPlatformSearchExtra\API\Values\Content\Search\WordSuggestion[] $expectedWordSuggestions
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testFindLocations(LocationQuery $query, array $expectedWordSuggestions)
    {
        $searchService = $this->getSearchService(false);

        /** @var \Netgen\EzPlatformSearchExtra\API\Values\Content\Search\SearchResult $searchResult */
        $searchResult = $searchService->findLocations($query);

        $this->assertEquals($expectedWordSuggestions, $searchResult->suggestion->getSuggestions());
    }

    protected function getSearchService($initialInitializeFromScratch = true)
    {
        return $this->getRepository($initialInitializeFromScratch)->getSearchService();
    }
}
