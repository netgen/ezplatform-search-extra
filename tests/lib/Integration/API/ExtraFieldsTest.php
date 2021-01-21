<?php

namespace Netgen\EzPlatformSearchExtra\Tests\Integration\API;

use eZ\Publish\API\Repository\Tests\BaseTest;
use eZ\Publish\API\Repository\Values\Content\Query;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Search\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Netgen\EzPlatformSearchExtra\Tests\API\FullTextCriterion;

/**
 * @group extra-fields
 */
class ExtraFieldsTest extends BaseTest
{
    public function providerForTestFind()
    {
        return [
            [
                new LocationQuery([
                    'query' => new FullTextCriterion('*comments*'),
                    'filter' => new Criterion\ContentTypeIdentifier('extra_fields_test'),
                    'extraFields' => [],
                ]),
                [],
            ],
            [
                new LocationQuery([
                    'query' => new FullTextCriterion('comments'),
                    'filter' => new Criterion\ContentTypeIdentifier('extra_fields_test'),
                    'extraFields' => ['extra_prefixed_name_s', 'extra_content_type_identifier_s'],
                ]),
                [
                    'extra_prefixed_name_s' => 'prefix No comments article',
                    'extra_content_type_identifier_s' => 'extra_fields_test',
                ],
            ],
            [
                new LocationQuery([
                    'query' => new FullTextCriterion('comments'),
                    'filter' => new Criterion\ContentTypeIdentifier('extra_fields_test'),
                    'extraFields' => ['extra_comment_count_i', 'extra_has_comments_b'],
                ]),
                [
                    'extra_comment_count_i' => 0,
                    'extra_has_comments_b' => false,
                ],
            ],
            [
                new LocationQuery([
                    'query' => new FullTextCriterion('comments'),
                    'filter' => new Criterion\ContentTypeIdentifier('extra_fields_test'),
                    'extraFields' => ['extra_prefixed_name_s', 'extra_comment_count_i', 'extra_has_comments_b'],
                ]),
                [
                    'extra_prefixed_name_s' => 'prefix No comments article',
                    'extra_comment_count_i' => 0,
                    'extra_has_comments_b' => false,
                ],
            ],
            [
                new LocationQuery([
                    'query' => new FullTextCriterion('popular'),
                    'filter' => new Criterion\ContentTypeIdentifier('extra_fields_test'),
                    'extraFields' => ['extra_prefixed_name_s', 'extra_comment_count_i', 'extra_has_comments_b'],
                ]),
                [
                    'extra_prefixed_name_s' => 'prefix Very popular article',
                    'extra_comment_count_i' => 6,
                    'extra_has_comments_b' => true,
                ],
            ],
            [
                new LocationQuery([
                    'query' => new FullTextCriterion('another'),
                    'filter' => new Criterion\ContentTypeIdentifier('extra_fields_test'),
                    'extraFields' => ['extra_prefixed_name_s', 'extra_comment_count_i', 'extra_has_comments_b', 'extra_content_type_identifier_s'],
                ]),
                [
                    'extra_prefixed_name_s' => 'prefix Just another article',
                    'extra_comment_count_i' => 2,
                    'extra_has_comments_b' => true,
                    'extra_content_type_identifier_s' => 'extra_fields_test',
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
        $contentTypeCreateStruct = $contentTypeService->newContentTypeCreateStruct('extra_fields_test');
        $contentTypeCreateStruct->mainLanguageCode = 'eng-GB';
        $contentTypeCreateStruct->names = ['eng-GB' => 'Article'];

        $fieldDefinitionCreateStruct = $contentTypeService->newFieldDefinitionCreateStruct('name', 'ezstring');
        $fieldDefinitionCreateStruct->position = 0;
        $contentTypeCreateStruct->addFieldDefinition($fieldDefinitionCreateStruct);

        $contentTypeDraft = $contentTypeService->createContentType($contentTypeCreateStruct, [reset($contentTypeGroups)]);
        $contentTypeService->publishContentTypeDraft($contentTypeDraft);
        $contentType = $contentTypeService->loadContentTypeByIdentifier('extra_fields_test');

        $contentTypeCreateStruct = $contentTypeService->newContentTypeCreateStruct('extra_fields_test_comment');
        $contentTypeCreateStruct->mainLanguageCode = 'eng-GB';
        $contentTypeCreateStruct->names = ['eng-GB' => 'Comment'];

        $fieldDefinitionCreateStruct = $contentTypeService->newFieldDefinitionCreateStruct('comment', 'ezstring');
        $fieldDefinitionCreateStruct->position = 0;
        $contentTypeCreateStruct->addFieldDefinition($fieldDefinitionCreateStruct);

        $contentTypeDraft = $contentTypeService->createContentType($contentTypeCreateStruct, [reset($contentTypeGroups)]);
        $contentTypeService->publishContentTypeDraft($contentTypeDraft);
        $commentContentType = $contentTypeService->loadContentTypeByIdentifier('extra_fields_test_comment');

        $values = [
            'No comments article' => [],
            'Very popular article' => ['comment 1', 'another comment', 'test comment', 'comment on comment', 'comment 2', 'test'],
            'Just another article' => ['first comment', 'second comment'],
        ];

        $locationCreateStruct = $locationService->newLocationCreateStruct(2);

        foreach ($values as $title => $comments) {
            $contentCreateStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
            $contentCreateStruct->setField('name', $title);
            $contentDraft = $contentService->createContent($contentCreateStruct, [$locationCreateStruct]);
            $articleContent = $contentService->publishVersion($contentDraft->versionInfo);

            foreach ($comments as $comment) {
                $commentLocationCreateStruct = $locationService->newLocationCreateStruct($articleContent->contentInfo->mainLocationId);
                $commentContentCreateStruct = $contentService->newContentCreateStruct($commentContentType, 'eng-GB');
                $commentContentCreateStruct->setField('comment', $comment);
                $commentContentDraft = $contentService->createContent($commentContentCreateStruct, [$commentLocationCreateStruct]);
                $contentService->publishVersion($commentContentDraft->versionInfo);
            }
        }

        $this->refreshSearch($repository);

        $this->assertTrue(true);
    }

    /**
     * @dataProvider providerForTestFind
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     * @param array $expectedExtraFields
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testFindContent(Query $query, array $expectedExtraFields)
    {
        $searchService = $this->getSearchService(false);

        /** @var \Netgen\EzPlatformSearchExtra\API\Values\Content\Search\SearchResult $searchResult */
        $searchResult = $searchService->findContentInfo($query);

        $this->assertEquals($expectedExtraFields, $searchResult->searchHits[0]->extraFields);
    }

    /**
     * @dataProvider providerForTestFind
     *
     * @param \Netgen\EzPlatformSearchExtra\API\Values\Content\Search\LocationQuery $query
     * @param array $expectedExtraFields
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testFindLocations(LocationQuery $query, array $expectedExtraFields)
    {
        $searchService = $this->getSearchService(false);

        /** @var \Netgen\EzPlatformSearchExtra\API\Values\Content\Search\SearchResult $searchResult */
        $searchResult = $searchService->findLocations($query);

        $this->assertEquals($expectedExtraFields, $searchResult->searchHits[0]->extraFields);
    }

    protected function getSearchService($initialInitializeFromScratch = true)
    {
        return $this->getRepository($initialInitializeFromScratch)->getSearchService();
    }
}
