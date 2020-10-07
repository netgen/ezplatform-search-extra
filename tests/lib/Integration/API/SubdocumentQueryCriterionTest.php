<?php

namespace Netgen\EzPlatformSearchExtra\Tests\Integration\API;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentId;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\CustomField;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalNot;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\ContentId as ContentIdSortClause;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\SubdocumentQuery;

/**
 * @see \Netgen\EzPlatformSearchExtra\Tests\Integration\Implementation\Solr\SubdocumentMapper\TestContentSubdocumentMapper
 * @see \Netgen\EzPlatformSearchExtra\Tests\Integration\Implementation\Solr\SubdocumentMapper\TestContentTranslationSubdocumentMapper
 */
class SubdocumentQueryCriterionTest extends BaseTest
{
    public function providerForTestFind()
    {
        return [
            [
                new Query([
                    'filter' => new LogicalAnd([
                        new ContentId([4, 12, 13, 42, 59]),
                        new SubdocumentQuery(
                            'test_content_subdocument',
                            new LogicalAnd([
                                new CustomField('visible_b', Operator::EQ, true),
                                new CustomField('price_i', Operator::EQ, 40),
                            ])
                        ),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [],
                [12],
            ],
            [
                new Query([
                    'filter' => new LogicalAnd([
                        new ContentId([4, 12, 13, 42, 59]),
                        new SubdocumentQuery(
                            'test_content_subdocument',
                            new LogicalNot(
                                new LogicalAnd([
                                    new CustomField('visible_b', Operator::EQ, true),
                                    new CustomField('price_i', Operator::EQ, 40),
                                ])
                            )
                        ),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [],
                [12, 42],
            ],
            [
                new Query([
                    'filter' => new LogicalAnd([
                        new ContentId([4, 12, 13, 42, 59]),
                        new SubdocumentQuery(
                            'test_content_subdocument',
                            new LogicalNot(
                                new LogicalAnd([
                                    new CustomField('visible_b', Operator::EQ, false),
                                    new CustomField('price_i', Operator::EQ, 50),
                                ])
                            )
                        ),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [],
                [12, 42],
            ],
            [
                new Query([
                    'filter' => new LogicalAnd([
                        new ContentId([4, 12, 13, 42, 59]),
                        new LogicalNot(
                            new SubdocumentQuery(
                                'test_content_subdocument',
                                new LogicalAnd([
                                    new CustomField('visible_b', Operator::EQ, true),
                                    new CustomField('price_i', Operator::EQ, 40),
                                ])
                            )
                        ),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [],
                [4, 13, 42, 59],
            ],
            [
                new Query([
                    'filter' => new LogicalAnd([
                        new ContentId([4, 12, 13, 42, 59]),
                        new LogicalNot(
                            new SubdocumentQuery(
                                'test_content_subdocument',
                                new LogicalAnd([
                                    new CustomField('visible_b', Operator::EQ, false),
                                    new CustomField('price_i', Operator::EQ, 50),
                                ])
                            )
                        ),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [],
                [4, 13, 59],
            ],
            [
                new Query([
                    'filter' => new LogicalAnd([
                        new ContentId([4, 12, 13, 42, 59]),
                        new SubdocumentQuery(
                            'test_content_translation_subdocument',
                            new CustomField('visible_b', Operator::EQ, true)
                        ),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                [],
                [],
            ],
            [
                new Query([
                    'filter' => new LogicalAnd([
                        new ContentId([4, 12, 13, 42, 59]),
                        new SubdocumentQuery(
                            'test_content_translation_subdocument',
                            new CustomField('visible_b', Operator::EQ, true)
                        ),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                ['languages' => ['ger-DE']],
                [4, 59],
            ],
            [
                new Query([
                    'filter' => new LogicalAnd([
                        new ContentId([4, 12, 13, 42, 59]),
                        new SubdocumentQuery(
                            'test_content_translation_subdocument',
                            new LogicalAnd([
                                new CustomField('visible_b', Operator::EQ, true),
                                new CustomField('price_i', Operator::EQ, 40),
                            ])
                        ),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                ['languages' => ['ger-DE']],
                [59],
            ],
            [
                new Query([
                    'filter' => new LogicalAnd([
                        new ContentId([4, 12, 13, 42, 59]),
                        new SubdocumentQuery(
                            'test_content_translation_subdocument',
                            new LogicalNot(
                                new LogicalAnd([
                                    new CustomField('visible_b', Operator::EQ, true),
                                    new CustomField('price_i', Operator::EQ, 40),
                                ])
                            )
                        ),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                ['languages' => ['ger-DE']],
                [4, 59],
            ],
            [
                new Query([
                    'filter' => new LogicalAnd([
                        new ContentId([4, 12, 13, 42, 59]),
                        new SubdocumentQuery(
                            'test_content_translation_subdocument',
                            new LogicalNot(
                                new LogicalAnd([
                                    new CustomField('visible_b', Operator::EQ, false),
                                    new CustomField('price_i', Operator::EQ, 50),
                                ])
                            )
                        ),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                ['languages' => ['ger-DE']],
                [4, 59],
            ],
            [
                new Query([
                    'filter' => new LogicalAnd([
                        new ContentId([4, 12, 13, 42, 59]),
                        new LogicalNot(
                            new SubdocumentQuery(
                                'test_content_translation_subdocument',
                                new LogicalAnd([
                                    new CustomField('visible_b', Operator::EQ, true),
                                    new CustomField('price_i', Operator::EQ, 40),
                                ])
                            )
                        ),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                ['languages' => ['ger-DE']],
                [4, 12, 13, 42],
            ],
            [
                new Query([
                    'filter' => new LogicalAnd([
                        new ContentId([4, 12, 13, 42, 59]),
                        new LogicalNot(
                            new SubdocumentQuery(
                                'test_content_translation_subdocument',
                                new LogicalAnd([
                                    new CustomField('visible_b', Operator::EQ, false),
                                    new CustomField('price_i', Operator::EQ, 50),
                                ])
                            )
                        ),
                    ]),
                    'sortClauses' => [new ContentIdSortClause()],
                ]),
                ['languages' => ['ger-DE']],
                [12, 13, 42],
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
        $updateStruct->setField('name', 'Benutzer', 'ger-DE');
        $contentService->updateContent($draft->versionInfo, $updateStruct);
        $contentService->publishVersion($draft->versionInfo);

        $contentInfo = $contentService->loadContentInfo(59);
        $draft = $contentService->createContentDraft($contentInfo);
        $updateStruct = $contentService->newContentUpdateStruct();
        $updateStruct->setField('name', 'Partner', 'ger-DE');
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
}
