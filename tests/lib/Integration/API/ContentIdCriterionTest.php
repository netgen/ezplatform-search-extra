<?php

namespace Netgen\EzPlatformSearchExtra\Tests\Integration\API;

use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\ContentId as ContentIdSortClause;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\ContentId;

class ContentIdCriterionTest extends BaseTest
{
    public function providerForTestFind()
    {
        return [
            [
                new LocationQuery([
                    'filter' => new ContentId(Operator::EQ, 14),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [14],
            ],
            [
                new LocationQuery([
                    'filter' => new ContentId(Operator::IN, [4, 10, 14, 41, 50, 57]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [4, 10, 14, 41, 50, 57],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId(Operator::IN, [4, 10, 14, 41, 50, 57]),
                        new ContentId(Operator::GT, 14),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [41, 50, 57],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId(Operator::IN, [4, 10, 14, 41, 50, 57]),
                        new ContentId(Operator::GTE, 14),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [14, 41, 50, 57],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId(Operator::IN, [4, 10, 14, 41, 50, 57]),
                        new ContentId(Operator::LT, 41),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [4, 10, 14],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId(Operator::IN, [4, 10, 14, 41, 50, 57]),
                        new ContentId(Operator::LTE, 41),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [4, 10, 14, 41],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId(Operator::IN, [4, 10, 14, 41, 50, 57]),
                        new ContentId(Operator::BETWEEN, [14, 50]),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [14, 41, 50],
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
    public function testFindLocations(LocationQuery $query, array $expectedIds)
    {
        $searchService = $this->getSearchService();

        $searchResult = $searchService->findLocations($query);

        $this->assertSearchResultContentIds($searchResult, $expectedIds);
    }
}
