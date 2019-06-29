<?php

namespace Netgen\EzPlatformSearchExtra\Tests\Integration\API;

use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentId;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\ContentId as ContentIdSortClause;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\UserEmail;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\UserLogin;

class UserLoginCriterionTest extends BaseTest
{
    public function providerForTestFind()
    {
        return [
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([10, 14, 41]),
                        new UserLogin(Operator::EQ, 'anonymous'),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [10],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([10, 14, 41]),
                        new UserLogin(Operator::EQ, 'admin'),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [14],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([10, 14, 41]),
                        new UserLogin(Operator::EQ, 'serverina'),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([10, 14, 41]),
                        new UserLogin(
                            Operator::IN,
                            [
                                'anonymous',
                                'serverina',
                            ]
                        ),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [10],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([10, 14, 41]),
                        new UserLogin(
                            Operator::IN,
                            [
                                'admin',
                                'serverina',
                            ]
                        ),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [14],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([10, 14, 41]),
                        new UserLogin(
                            Operator::IN,
                            [
                                'admin',
                                'anonymous',
                            ]
                        ),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [10, 14],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([10, 14, 41]),
                        new UserLogin(
                            Operator::LIKE,
                            'a*'
                        ),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [10, 14],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([10, 14, 41]),
                        new UserLogin(
                            Operator::LIKE,
                            'ad*'
                        ),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [14],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([10, 14, 41]),
                        new UserLogin(
                            Operator::LIKE,
                            'anon*'
                        ),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [10],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([10, 14, 41]),
                        new UserLogin(
                            Operator::LIKE,
                            '*m*'
                        ),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [10, 14],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([10, 14, 41]),
                        new UserLogin(
                            Operator::LIKE,
                            '*mous'
                        ),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [10],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([10, 14, 41]),
                        new UserLogin(
                            Operator::LIKE,
                            '*in'
                        ),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [14],
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
