<?php

namespace Netgen\EzPlatformSearchExtra\Tests\Integration\Solr;

use eZ\Publish\API\Repository\Tests\BaseTest;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentId;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\CustomField;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use Netgen\EzPlatformSearchExtra\Core\Search\Solr\API\Facet\RawFacet;
use Netgen\EzPlatformSearchExtra\Core\Search\Solr\API\FacetBuilder\RawFacetBuilder;
use Netgen\EzPlatformSearchExtra\Core\Search\Solr\API\FacetBuilder\RawFacetBuilder\Domain\BlockChildren;

class RawFacetDomainTest extends BaseTest
{
    public function providerForTestFind()
    {
        return [
            [
                new Query([
                    'facetBuilders' => [
                        new RawFacetBuilder([
                            'name' => 'test_facet',
                            'parameters' => [
                                'type' => 'terms',
                                'field' => 'price_i',
                                'allBuckets' => true,
                                'sort' => 'count desc',
                                'facet' => [
                                    'maximum' => 'max(price_i)',
                                    'minimum' => 'min(price_i)',
                                ],
                            ],
                            'domain' => new BlockChildren([
                                'parentDocumentIdentifier' => 'content',
                                'childDocumentIdentifier' => 'test_content_subdocument',
                                'filter' => null,
                            ]),
                        ]),
                    ],
                    'filter' => new ContentId([4, 12, 13, 42, 59]),
                    'limit' => 0,
                ]),
                [
                    new RawFacet([
                        'name' => 'test_facet',
                        'data' => json_decode(
                            json_encode(
                                [
                                    'allBuckets' => [
                                        'count' => 4,
                                        'maximum' => 60.0,
                                        'minimum' => 40.0,
                                    ],
                                    'buckets' => [
                                        [
                                            'val' => 50,
                                            'count' => 2,
                                            'maximum' => 50.0,
                                            'minimum' => 50.0,
                                        ],
                                        [
                                            'val' => 40,
                                            'count' => 1,
                                            'maximum' => 40.0,
                                            'minimum' => 40.0,
                                        ],
                                        [
                                            'val' => 60,
                                            'count' => 1,
                                            'maximum' => 60.0,
                                            'minimum' => 60.0,
                                        ],
                                    ],
                                ]
                            )
                        ),
                    ]),
                ],
            ],
            [
                new Query([
                    'facetBuilders' => [
                        new RawFacetBuilder([
                            'name' => 'test_facet',
                            'parameters' => [
                                'type' => 'terms',
                                'field' => 'price_i',
                                'allBuckets' => true,
                                'sort' => 'count desc',
                                'facet' => [
                                    'maximum' => 'max(price_i)',
                                    'minimum' => 'min(price_i)',
                                ],
                            ],
                            'domain' => new BlockChildren([
                                'parentDocumentIdentifier' => 'content',
                                'childDocumentIdentifier' => 'test_content_subdocument',
                                'filter' => new CustomField('visible_b', Operator::EQ, true),
                            ]),
                        ]),
                    ],
                    'filter' => new ContentId([4, 12, 13, 42, 59]),
                    'limit' => 0,
                ]),
                [
                    new RawFacet([
                        'name' => 'test_facet',
                        'data' => json_decode(
                            json_encode(
                                [
                                    'allBuckets' => [
                                        'count' => 2,
                                        'maximum' => 60.0,
                                        'minimum' => 40.0,
                                    ],
                                    'buckets' => [
                                        [
                                            'val' => 40,
                                            'count' => 1,
                                            'maximum' => 40.0,
                                            'minimum' => 40.0,
                                        ],
                                        [
                                            'val' => 60,
                                            'count' => 1,
                                            'maximum' => 60.0,
                                            'minimum' => 60.0,
                                        ],
                                    ],
                                ]
                            )
                        ),
                    ]),
                ],
            ],
            [
                new Query([
                    'facetBuilders' => [
                        new RawFacetBuilder([
                            'name' => 'test_facet',
                            'parameters' => [
                                'type' => 'terms',
                                'field' => 'price_i',
                                'allBuckets' => true,
                                'sort' => 'count desc',
                                'facet' => [
                                    'maximum' => 'max(price_i)',
                                    'minimum' => 'min(price_i)',
                                ],
                            ],
                            'domain' => new BlockChildren([
                                'parentDocumentIdentifier' => 'content',
                                'childDocumentIdentifier' => 'test_content_subdocument',
                                'filter' => new CustomField('visible_b', Operator::EQ, false),
                            ]),
                        ]),
                    ],
                    'filter' => new ContentId([4, 12, 13, 42, 59]),
                    'limit' => 0,
                ]),
                [
                    new RawFacet([
                        'name' => 'test_facet',
                        'data' => json_decode(
                            json_encode(
                                [
                                    'allBuckets' => [
                                        'count' => 2,
                                        'maximum' => 50.0,
                                        'minimum' => 50.0,
                                    ],
                                    'buckets' => [
                                        [
                                            'val' => 50,
                                            'count' => 2,
                                            'maximum' => 50.0,
                                            'minimum' => 50.0,
                                        ],
                                    ],
                                ]
                            )
                        ),
                    ]),
                ],
            ],
        ];
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

    protected function getSearchService($initialInitializeFromScratch = true)
    {
        return $this->getRepository($initialInitializeFromScratch)->getSearchService();
    }
}
