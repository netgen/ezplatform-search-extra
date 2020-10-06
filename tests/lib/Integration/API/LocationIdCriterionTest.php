<?php

namespace Netgen\EzPlatformSearchExtra\Tests\Integration\API;

use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\ContentId as ContentIdSortClause;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\LocationId;

class LocationIdCriterionTest extends BaseTest
{
    public function providerForTestFind()
    {
        return [
            [
                new LocationQuery([
                    'filter' => new LocationId(Operator::EQ, 12),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [12],
            ],
            [
                new LocationQuery([
                    'filter' => new LocationId(Operator::IN, [12, 13, 14, 15, 43, 44]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [12, 13, 14, 15, 43, 44],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new LocationId(Operator::IN, [12, 13, 14, 15, 43, 44]),
                        new LocationId(Operator::GT, 14),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [15, 43, 44],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new LocationId(Operator::IN, [12, 13, 14, 15, 43, 44]),
                        new LocationId(Operator::GTE, 14),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [14, 15, 43, 44],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new LocationId(Operator::IN, [12, 13, 14, 15, 43, 44]),
                        new LocationId(Operator::LT, 15),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [12, 13, 14],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new LocationId(Operator::IN, [12, 13, 14, 15, 43, 44]),
                        new LocationId(Operator::LTE, 15),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [12, 13, 14, 15],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new LocationId(Operator::IN, [12, 13, 14, 15, 43, 44]),
                        new LocationId(Operator::BETWEEN, [13, 15]),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [13, 14, 15],
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

        $this->assertSearchResultLocationIds($searchResult, $expectedIds);
    }

    /**
     * @dataProvider providerForTestFind
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LocationQuery $query
     * @param array $expectedIds
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testFindLocations(LocationQuery $query, array $expectedIds)
    {
        $searchService = $this->getSearchService();

        $searchResult = $searchService->findLocations($query);

        $this->assertSearchResultLocationIds($searchResult, $expectedIds);
    }
}
