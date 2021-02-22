<?php

namespace Netgen\EzPlatformSearchExtra\Tests\Integration\API;

use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentId;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\ContentId as ContentIdSortClause;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\UserEmail;

class UserEmailCriterionTest extends BaseTest
{
    public function providerForTestFind()
    {
        return [
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([10, 14, 41]),
                        new UserEmail(Operator::EQ, 'anonymous@link.invalid'),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [10],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([10, 14, 41]),
                        new UserEmail(Operator::EQ, 'admin@link.invalid'),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [14],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([10, 14, 41]),
                        new UserEmail(Operator::EQ, 'jam@ez.no'),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentId([10, 14, 41]),
                        new UserEmail(
                            Operator::IN,
                            [
                                'anonymous@link.invalid',
                                'jam@ez.no',
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
                        new UserEmail(
                            Operator::IN,
                            [
                                'admin@link.invalid',
                                'jam@ez.no',
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
                        new UserEmail(
                            Operator::IN,
                            [
                                'admin@link.invalid',
                                'anonymous@link.invalid',
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
                        new UserEmail(
                            Operator::LIKE,
                            '*invalid'
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
                        new UserEmail(
                            Operator::LIKE,
                            'admin*'
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
                        new UserEmail(
                            Operator::LIKE,
                            'anonymous*'
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
                        new UserEmail(
                            Operator::LIKE,
                            '*link*'
                        ),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [10, 14],
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
