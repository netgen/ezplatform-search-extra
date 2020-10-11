<?php

declare(strict_types=1);

namespace Netgen\EzPlatformSearchExtra\Tests\Integration\API;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\ContentId;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\SectionIdentifier;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\ValueObject;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\SortClause\ContentName;
use RuntimeException;

class ContentNameSortClauseTest extends BaseTest
{
    public function providerForTestFind(): array
    {
        return [
            [
                new LocationQuery([
                    'filter' => new ContentTypeIdentifier('name_test'),
                    'sortClauses' => [
                        new ContentName(Query::SORT_ASC),
                        new SectionIdentifier(),
                    ],
                ]),
                ['eng-GB'],
                false,
                ['e1', 'e2', 'e4', 'e7'],
            ],
            [
                new LocationQuery([
                    'filter' => new ContentTypeIdentifier('name_test'),
                    'sortClauses' => [
                        new SectionIdentifier(),
                        new ContentName(Query::SORT_DESC),
                    ],
                ]),
                ['eng-GB'],
                false,
                ['e7', 'e4', 'e2', 'e1'],
            ],
            [
                new LocationQuery([
                    'filter' => new ContentTypeIdentifier('name_test'),
                    'sortClauses' => [
                        new ContentId(),
                        new ContentName(Query::SORT_ASC),
                    ],
                ]),
                ['eng-GB'],
                true,
                ['e1', 'e2', 'n3', 'e4', 'n5', 'e7'],
            ],
            [
                new LocationQuery([
                    'filter' => new ContentTypeIdentifier('name_test'),
                    'sortClauses' => [new ContentName(Query::SORT_ASC)],
                ]),
                ['eng-GB'],
                true,
                ['e1', 'e2', 'e4', 'e7', 'n3', 'n5'],
            ],
            [
                new LocationQuery([
                    'filter' => new ContentTypeIdentifier('name_test'),
                    'sortClauses' => [
                        new ContentName(Query::SORT_DESC),
                        new ContentId(),
                    ],
                ]),
                ['eng-GB'],
                true,
                ['n5', 'n3', 'e7', 'e4', 'e2', 'e1'],
            ],
            [
                new LocationQuery([
                    'filter' => new ContentTypeIdentifier('name_test'),
                    'sortClauses' => [new ContentName(Query::SORT_ASC)],
                ]),
                ['nor-NO'],
                false,
                ['n2', 'n3', 'n5', 'n7'],
            ],
            [
                new LocationQuery([
                    'filter' => new ContentTypeIdentifier('name_test'),
                    'sortClauses' => [new ContentName(Query::SORT_DESC)],
                ]),
                ['nor-NO'],
                false,
                ['n7', 'n5', 'n3', 'n2'],
            ],
            [
                new LocationQuery([
                    'filter' => new ContentTypeIdentifier('name_test'),
                    'sortClauses' => [new ContentName(Query::SORT_ASC)],
                ]),
                ['ger-DE'],
                false,
                ['g1', 'g3', 'g6', 'g7'],
            ],
            [
                new LocationQuery([
                    'filter' => new ContentTypeIdentifier('name_test'),
                    'sortClauses' => [new ContentName(Query::SORT_DESC)],
                ]),
                ['ger-DE'],
                false,
                ['g7', 'g6', 'g3', 'g1'],
            ],
            [
                new LocationQuery([
                    'filter' => new ContentTypeIdentifier('name_test'),
                    'sortClauses' => [new ContentName(Query::SORT_ASC)],
                ]),
                ['ger-DE'],
                true,
                ['g1', 'g3', 'g6', 'g7', 'n2', 'n5'],
            ],
            [
                new LocationQuery([
                    'filter' => new ContentTypeIdentifier('name_test'),
                    'sortClauses' => [new ContentName(Query::SORT_DESC)],
                ]),
                ['ger-DE'],
                true,
                ['n5', 'n2', 'g7', 'g6', 'g3', 'g1'],
            ],
            [
                new LocationQuery([
                    'filter' => new ContentTypeIdentifier('name_test'),
                    'sortClauses' => [new ContentName(Query::SORT_ASC)],
                ]),
                ['eng-GB', 'ger-DE'],
                false,
                ['e1', 'e2', 'e4', 'e7', 'g3', 'g6'],
            ],
            [
                new LocationQuery([
                    'filter' => new ContentTypeIdentifier('name_test'),
                    'sortClauses' => [new ContentName(Query::SORT_DESC)],
                ]),
                ['eng-GB', 'ger-DE'],
                false,
                ['g6', 'g3', 'e7', 'e4', 'e2', 'e1'],
            ],
            [
                new LocationQuery([
                    'filter' => new ContentTypeIdentifier('name_test'),
                    'sortClauses' => [new ContentName(Query::SORT_ASC)],
                ]),
                ['ger-DE', 'eng-GB'],
                false,
                ['e2', 'e4', 'g1', 'g3', 'g6', 'g7'],
            ],
            [
                new LocationQuery([
                    'filter' => new ContentTypeIdentifier('name_test'),
                    'sortClauses' => [new ContentName(Query::SORT_DESC)],
                ]),
                ['ger-DE', 'eng-GB'],
                false,
                ['g7', 'g6', 'g3', 'g1', 'e4', 'e2'],
            ],
            [
                new LocationQuery([
                    'filter' => new ContentTypeIdentifier('name_test'),
                    'sortClauses' => [new ContentName(Query::SORT_ASC)],
                ]),
                ['ger-DE', 'eng-GB', 'nor-NO'],
                false,
                ['e2', 'e4', 'g1', 'g3', 'g6', 'g7', 'n5'],
            ],
            [
                new LocationQuery([
                    'filter' => new ContentTypeIdentifier('name_test'),
                    'sortClauses' => [new ContentName(Query::SORT_DESC)],
                ]),
                ['ger-DE', 'eng-GB', 'nor-NO'],
                false,
                ['n5', 'g7', 'g6', 'g3', 'g1', 'e4', 'e2'],
            ],
            [
                new LocationQuery([
                    'filter' => new ContentTypeIdentifier('name_test'),
                    'sortClauses' => [new ContentName(Query::SORT_ASC)],
                ]),
                ['ger-DE', 'nor-NO', 'eng-GB'],
                false,
                ['e4', 'g1', 'g3', 'g6', 'g7', 'n2', 'n5'],
            ],
            [
                new LocationQuery([
                    'filter' => new ContentTypeIdentifier('name_test'),
                    'sortClauses' => [new ContentName(Query::SORT_DESC)],
                ]),
                ['ger-DE', 'nor-NO', 'eng-GB'],
                false,
                ['n5', 'n2', 'g7', 'g6', 'g3', 'g1', 'e4'],
            ],
            [
                new LocationQuery([
                    'filter' => new ContentTypeIdentifier('name_test'),
                    'sortClauses' => [new ContentName(Query::SORT_ASC)],
                ]),
                ['nor-NO', 'ger-DE', 'eng-GB'],
                false,
                ['e4', 'g1', 'g6', 'n2', 'n3', 'n5', 'n7'],
            ],
            [
                new LocationQuery([
                    'filter' => new ContentTypeIdentifier('name_test'),
                    'sortClauses' => [new ContentName(Query::SORT_DESC)],
                ]),
                ['nor-NO', 'ger-DE', 'eng-GB'],
                false,
                ['n7', 'n5', 'n3', 'n2', 'g6', 'g1', 'e4'],
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
            ['e1', 'g1'],
            ['n2', 'e2'],
            ['n3', 'g3'],
            ['e4'],
            ['n5'],
            ['g6'],
            ['n7', 'e7', 'g7'],
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
