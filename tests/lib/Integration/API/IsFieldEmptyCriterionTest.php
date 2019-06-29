<?php

namespace Netgen\EzPlatformSearchExtra\Tests\Integration\API;

use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentId;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalNot;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\ContentId as ContentIdSortClause;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\IsFieldEmpty;

class IsFieldEmptyCriterionTest extends BaseTest
{
    public function providerForTestFind()
    {
        return [
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([4, 11, 42]),
                        new IsFieldEmpty('description', IsFieldEmpty::IS_NOT_EMPTY),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [],
                [4, 42],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([4, 11, 42]),
                        new IsFieldEmpty('description', IsFieldEmpty::IS_EMPTY),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [],
                [11],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([4, 11, 42]),
                        new LogicalNot(
                            new IsFieldEmpty('description', IsFieldEmpty::IS_NOT_EMPTY)
                        ),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [],
                [11],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([4, 11, 42]),
                        new LogicalNot(
                            new IsFieldEmpty('description', IsFieldEmpty::IS_EMPTY)
                        ),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [],
                [4, 42],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([4, 11, 42]),
                        new IsFieldEmpty('description', IsFieldEmpty::IS_NOT_EMPTY),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                ['languages' => ['ger-DE']],
                [11],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([4, 11, 42]),
                        new IsFieldEmpty('description', IsFieldEmpty::IS_EMPTY),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                ['languages' => ['ger-DE']],
                [4, 42],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([4, 11, 42]),
                        new LogicalNot(
                            new IsFieldEmpty('description', IsFieldEmpty::IS_NOT_EMPTY)
                        ),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                ['languages' => ['ger-DE']],
                [4, 42],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([4, 11, 42]),
                        new LogicalNot(
                            new IsFieldEmpty('description', IsFieldEmpty::IS_EMPTY)
                        ),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                ['languages' => ['ger-DE']],
                [11],
            ],
        ];
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testPrepareTestFixtures()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();

        $contentInfo = $contentService->loadContentInfo(4);
        $draft = $contentService->createContentDraft($contentInfo);
        $updateStruct = $contentService->newContentUpdateStruct();
        $updateStruct->setField('name', 'not empty', 'ger-DE');
        $updateStruct->setField('description', '', 'ger-DE');
        $contentService->updateContent($draft->versionInfo, $updateStruct);
        $contentService->publishVersion($draft->versionInfo);

        $contentInfo = $contentService->loadContentInfo(11);
        $draft = $contentService->createContentDraft($contentInfo);
        $updateStruct = $contentService->newContentUpdateStruct();
        $updateStruct->setField('name', 'not empty', 'ger-DE');
        $updateStruct->setField('description', 'not empty', 'ger-DE');
        $contentService->updateContent($draft->versionInfo, $updateStruct);
        $contentService->publishVersion($draft->versionInfo);

        $contentInfo = $contentService->loadContentInfo(42);
        $draft = $contentService->createContentDraft($contentInfo);
        $updateStruct = $contentService->newContentUpdateStruct();
        $updateStruct->setField('name', 'not empty', 'ger-DE');
        $updateStruct->setField('description', '', 'ger-DE');
        $contentService->updateContent($draft->versionInfo, $updateStruct);
        $contentService->publishVersion($draft->versionInfo);

        $this->refreshSearch($repository);

        $this->assertTrue(true);
    }

    /**
     * @dataProvider providerForTestFind
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     * @param array $languageFilter
     * @param array $expectedIds
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testFindContent(Query $query, array $languageFilter, array $expectedIds)
    {
        $searchService = $this->getSearchService(false);

        $searchResult = $searchService->findContentInfo($query, $languageFilter);

        $this->assertSearchResultContentIds($searchResult, $expectedIds);
    }

    /**
     * @dataProvider providerForTestFind
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LocationQuery $query
     * @param array $languageFilter
     * @param array $expectedIds
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testFindLocations(LocationQuery $query, array $languageFilter, $expectedIds)
    {
        $searchService = $this->getSearchService(false);

        $searchResult = $searchService->findLocations($query, $languageFilter);

        $this->assertSearchResultContentIds($searchResult, $expectedIds);
    }
}
