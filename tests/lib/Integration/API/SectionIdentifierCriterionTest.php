<?php

namespace Netgen\EzPlatformSearchExtra\Tests\Integration\API;

use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentId;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalNot;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\ContentId as ContentIdSortClause;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\SectionIdentifier;

class SectionIdentifierCriterionTest extends BaseTest
{
    public function providerForTestFind()
    {
        return [
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([4, 50, 57]),
                        new SectionIdentifier('standard'),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [57],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([4, 50, 57]),
                        new SectionIdentifier('users'),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [4],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([4, 50, 57]),
                        new SectionIdentifier(['standard', 'users']),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [4, 57],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([4, 50, 57]),
                        new SectionIdentifier('setup'),
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
                            new SectionIdentifier('standard')
                        ),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [4, 50],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([4, 50, 57]),
                        new LogicalNot(
                            new SectionIdentifier('users')
                        ),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [50, 57],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([4, 50, 57]),
                        new LogicalNot(
                            new SectionIdentifier(['standard', 'users'])
                        ),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [50],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([4, 50, 57]),
                        new LogicalNot(
                            new SectionIdentifier('setup')
                        ),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [4, 50, 57],
            ],
        ];
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
        $searchService = $this->getSearchService();

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
        $searchService = $this->getSearchService();

        $searchResult = $searchService->findLocations($query);

        $this->assertSearchResultContentIds($searchResult, $expectedIds);
    }
}
