<?php

declare(strict_types=1);

namespace Netgen\EzPlatformSearchExtra\Tests\Integration\API;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOr;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\ContentId;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\SectionIdentifier;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\ValueObject;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\ContentName;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\SortClause\ContentName as ContentNameSortClause;
use RuntimeException;

class ContentNameCriterionTest extends BaseTest
{
    public function providerForTestFind(): array
    {
        return [
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentTypeIdentifier('name_test'),
                        new ContentName(Operator::EQ, 'ev2'),
                    ]),
                    'sortClauses' => [
                        new ContentNameSortClause(Query::SORT_ASC),
                        new SectionIdentifier(),
                    ],
                ]),
                ['eng-GB'],
                false,
                ['ev2'],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentTypeIdentifier('name_test'),
                        new ContentName(Operator::GT, 'ev2'),
                    ]),
                    'sortClauses' => [
                        new SectionIdentifier(),
                        new ContentNameSortClause(Query::SORT_DESC),
                    ],
                ]),
                ['eng-GB'],
                false,
                ['ev7', 'ev4'],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentTypeIdentifier('name_test'),
                        new ContentName(Operator::GTE, 'nx3'),
                    ]),
                    'sortClauses' => [
                        new ContentId(),
                        new ContentNameSortClause(Query::SORT_ASC),
                    ],
                ]),
                ['eng-GB'],
                true,
                ['nx3', 'nx5'],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentTypeIdentifier('name_test'),
                        new ContentName(Operator::LT, 'ev4'),
                    ]),
                    'sortClauses' => [new ContentNameSortClause(Query::SORT_ASC)],
                ]),
                ['eng-GB'],
                true,
                ['ev1', 'ev2'],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentTypeIdentifier('name_test'),
                        new ContentName(Operator::LTE, 'nx3'),
                    ]),
                    'sortClauses' => [
                        new ContentNameSortClause(Query::SORT_DESC),
                        new ContentId(),
                    ],
                ]),
                ['eng-GB'],
                true,
                ['nx3', 'ev7', 'ev4', 'ev2', 'ev1'],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentTypeIdentifier('name_test'),
                        new ContentName(Operator::BETWEEN, ['nx2', 'nx5']),
                    ]),
                    'sortClauses' => [new ContentNameSortClause(Query::SORT_ASC)],
                ]),
                ['nor-NO'],
                false,
                ['nx2', 'nx3', 'nx5'],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentTypeIdentifier('name_test'),
                        new ContentName(Operator::GT, 'nx2'),
                        new ContentName(Operator::LTE, 'nx5'),
                    ]),
                    'sortClauses' => [new ContentNameSortClause(Query::SORT_DESC)],
                ]),
                ['nor-NO'],
                false,
                ['nx5', 'nx3'],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentTypeIdentifier('name_test'),
                        new ContentName(Operator::LT, 'gx6'),
                        new ContentName(Operator::LIKE, 'gx*'),
                    ]),
                    'sortClauses' => [new ContentNameSortClause(Query::SORT_ASC)],
                ]),
                ['ger-DE'],
                false,
                ['gx1', 'gx3'],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentTypeIdentifier('name_test'),
                        new LogicalOr([
                            new ContentName(Operator::LIKE, '*3'),
                            new ContentName(Operator::LIKE, '*6'),
                        ])
                    ]),
                    'sortClauses' => [new ContentNameSortClause(Query::SORT_DESC)],
                ]),
                ['ger-DE'],
                false,
                ['gx6', 'gx3'],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentTypeIdentifier('name_test'),
                        new ContentName(Operator::LIKE, '*n*'),
                    ]),
                    'sortClauses' => [new ContentNameSortClause(Query::SORT_ASC)],
                ]),
                ['ger-DE'],
                true,
                ['nx2', 'nx5'],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentTypeIdentifier('name_test'),
                        new ContentName(Operator::LIKE, '*v*'),
                    ]),
                    'sortClauses' => [new ContentNameSortClause(Query::SORT_ASC)],
                ]),
                ['eng-GB'],
                true,
                ['ev1', 'ev2', 'ev4', 'ev7'],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentTypeIdentifier('name_test'),
                        new ContentName(Operator::LIKE, 'nx*'),
                        new ContentName(Operator::GT, 'gx6'),
                    ]),
                    'sortClauses' => [new ContentNameSortClause(Query::SORT_DESC)],
                ]),
                ['ger-DE'],
                true,
                ['nx5', 'nx2'],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentTypeIdentifier('name_test'),
                        new ContentName(Operator::IN, ['ev2']),
                    ]),
                    'sortClauses' => [new ContentNameSortClause(Query::SORT_ASC)],
                ]),
                ['eng-GB', 'ger-DE'],
                false,
                ['ev2'],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentTypeIdentifier('name_test'),
                        new ContentName(Operator::IN, ['gx6', 'ev7', 'ev1']),
                    ]),
                    'sortClauses' => [new ContentNameSortClause(Query::SORT_DESC)],
                ]),
                ['eng-GB', 'ger-DE'],
                false,
                ['gx6', 'ev7', 'ev1'],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentTypeIdentifier('name_test'),
                        new ContentName(Operator::IN, ['gx6', 'ev2', 'gx7', 'ev4']),
                        new ContentName(Operator::GTE, 'ev4'),
                    ]),
                    'sortClauses' => [new ContentNameSortClause(Query::SORT_ASC)],
                ]),
                ['ger-DE', 'eng-GB'],
                false,
                ['ev4', 'gx6', 'gx7'],
            ],
            [
                new LocationQuery([
                    'filter' => new LogicalAnd([
                        new ContentTypeIdentifier('name_test'),
                        new ContentName(Operator::IN, ['gx6', 'gx3', 'ev4']),
                        new ContentName(Operator::IN, ['gx1', 'ev4', 'ev2']),
                    ]),
                    'sortClauses' => [new ContentNameSortClause(Query::SORT_DESC)],
                ]),
                ['ger-DE', 'eng-GB'],
                false,
                ['ev4'],
            ],
        ];
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testPrepareTestFixtures(): void
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();
        $locationService = $repository->getLocationService();
        $languageService = $repository->getContentLanguageService();

        $languageCreateStruct = $languageService->newLanguageCreateStruct();
        $languageCreateStruct->name = 'Norwegian';
        $languageCreateStruct->languageCode = 'nor-NO';
        $languageService->createLanguage($languageCreateStruct);

        $contentTypeGroups = $contentTypeService->loadContentTypeGroups();
        $contentTypeCreateStruct = $contentTypeService->newContentTypeCreateStruct('name_test');
        $contentTypeCreateStruct->mainLanguageCode = 'eng-GB';
        $contentTypeCreateStruct->names = ['eng-GB' => 'Name test type'];
        $contentTypeCreateStruct->nameSchema = '<title>';
        $fieldDefinitionCreateStruct = $contentTypeService->newFieldDefinitionCreateStruct('title', 'ezstring');
        $contentTypeCreateStruct->addFieldDefinition($fieldDefinitionCreateStruct);
        $contentTypeDraft = $contentTypeService->createContentType($contentTypeCreateStruct, [reset($contentTypeGroups)]);
        $contentTypeService->publishContentTypeDraft($contentTypeDraft);
        $contentType = $contentTypeService->loadContentTypeByIdentifier('name_test');

        $valueGroups = [
            ['ev1', 'gx1'],
            ['nx2', 'ev2'],
            ['nx3', 'gx3'],
            ['ev4'],
            ['nx5'],
            ['gx6'],
            ['nx7', 'ev7', 'gx7'],
        ];

        $locationCreateStruct = $locationService->newLocationCreateStruct(2);

        foreach ($valueGroups as $values) {
            $mainValue = reset($values);
            $mainLanguageCode = $this->resolveLanguageCode($mainValue);
            $contentCreateStruct = $contentService->newContentCreateStruct($contentType, $mainLanguageCode);
            $contentCreateStruct->alwaysAvailable = ($mainLanguageCode === 'nor-NO');

            foreach ($values as $value) {
                $languageCode = $this->resolveLanguageCode($value);
                $contentCreateStruct->setField('title', $value, $languageCode);
            }

            $contentDraft = $contentService->createContent($contentCreateStruct, [$locationCreateStruct]);
            $contentService->publishVersion($contentDraft->versionInfo);
        }

        $this->refreshSearch($repository);

        $this->addToAssertionCount(1);
    }

    /**
     * @dataProvider providerForTestFind
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     * @param string[] $languageCodes
     * @param bool $useAlwaysAvailable
     * @param array $expectedValues
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testFindContent(
        Query $query,
        array $languageCodes,
        bool $useAlwaysAvailable,
        array $expectedValues
    ): void {
        $searchService = $this->getSearchService(false);
        $languageFilter = ['languages' => $languageCodes, 'useAlwaysAvailable' => $useAlwaysAvailable];

        $searchResult = $searchService->findContent($query, $languageFilter);

        $this->assertSearchResult($searchResult, $expectedValues);
    }

    /**
     * @dataProvider providerForTestFind
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LocationQuery $query
     * @param string[] $languageCodes
     * @param bool $useAlwaysAvailable
     * @param array $expectedValues
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testFindLocations(
        LocationQuery $query,
        array $languageCodes,
        bool $useAlwaysAvailable,
        array $expectedValues
    ): void {
        $searchService = $this->getSearchService(false);
        $languageFilter = ['languages' => $languageCodes, 'useAlwaysAvailable' => $useAlwaysAvailable];

        $searchResult = $searchService->findLocations($query, $languageFilter);

        $this->assertSearchResult($searchResult, $expectedValues);
    }

    protected function assertSearchResult(SearchResult $searchResult, array $expectedValues): void
    {
        self::assertCount(count($expectedValues), $searchResult->searchHits);
        $actualValues = [];
        $actualNames = [];

        foreach ($expectedValues as $index => $value) {
            $searchHit = $searchResult->searchHits[$index];
            $content = $this->getContent($searchHit->valueObject);
            $languageCode = $this->resolveLanguageCode($value);
            /** @var \eZ\Publish\Core\FieldType\TextLine\Value $fieldValue */
            $fieldValue = $content->getFieldValue('title', $languageCode);

            $actualValues[] = $fieldValue->text ?? null;
            $actualNames[] = $content->getName($languageCode);
        }

        self::assertEquals($expectedValues, $actualValues);
        self::assertEquals($expectedValues, $actualNames);
    }

    protected function getContent(ValueObject $valueObject): Content
    {
        if ($valueObject instanceof Content) {
            return $valueObject;
        }

        if ($valueObject instanceof Location) {
            return $valueObject->getContent();
        }

        throw new RuntimeException('Could not resolve Content');
    }

    protected function resolveLanguageCode(string $value): string
    {
        switch ($value[0]) {
            case 'n';
                return 'nor-NO';
            case 'e';
                return 'eng-GB';
            case 'g';
                return 'ger-DE';
        }

        throw new RuntimeException('Could not resolve language code');
    }
}
