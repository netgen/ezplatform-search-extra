<?php

namespace Netgen\EzPlatformSearchExtra\Tests\Integration\API;

use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentId;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalNot;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOr;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\ContentId as ContentIdSortClause;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\ObjectStateIdentifier;

class ObjectStateIdentifierCriterionTest extends BaseTest
{
    public function providerForTestFind()
    {
        return [
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([4, 50, 57]),
                        new ObjectStateIdentifier('ez_lock', 'not_locked'),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [4, 57],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([4, 50, 57]),
                        new ObjectStateIdentifier('ez_lock', 'locked'),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [50],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([4, 50, 57]),
                        new LogicalOr([
                            new ObjectStateIdentifier('ez_lock', 'locked'),
                            new ObjectStateIdentifier('ez_lock', 'not_locked'),
                        ]),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [4, 50, 57],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([4, 57]),
                        new ObjectStateIdentifier('ez_lock', 'locked'),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([4, 50, 57]),
                        new LogicalNot(
                            new ObjectStateIdentifier('ez_lock', 'locked')
                        ),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [4, 57],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([4, 50, 57]),
                        new LogicalNot(
                            new ObjectStateIdentifier('ez_lock', 'not_locked')
                        ),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [50],
            ],
        ];
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testPrepareTestFixtures()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $objectStateService = $repository->getObjectStateService();

        $contentInfo = $contentService->loadContentInfo(50);
        $objectStateGroups = $objectStateService->loadObjectStateGroups();

        foreach ($objectStateGroups as $objectStateGroup) {
            if ($objectStateGroup->identifier === 'ez_lock') {
                break;
            }
        }

        /** @var \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup */
        $objectStates = $objectStateService->loadObjectStates($objectStateGroup);

        foreach ($objectStates as $objectState) {
            if ($objectState->identifier === 'locked') {
                break;
            }
        }

        /** @var \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $objectState */
        $objectStateService->setContentState($contentInfo, $objectStateGroup, $objectState);
        $this->refreshSearch($repository);

        $this->assertTrue(true);
    }

    /**
     * @dataProvider providerForTestFind
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     * @param array $expectedIds
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testFindContent(Query $query, array $expectedIds)
    {
        $searchService = $this->getSearchService(false);

        $searchResult = $searchService->findContentInfo($query);

        $this->assertSearchResultContentIds($searchResult, $expectedIds);
    }

    /**
     * @dataProvider providerForTestFind
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LocationQuery $query
     * @param array $expectedIds
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testFindLocations(LocationQuery $query, $expectedIds)
    {
        $searchService = $this->getSearchService(false);

        $searchResult = $searchService->findLocations($query);

        $this->assertSearchResultContentIds($searchResult, $expectedIds);
    }
}
