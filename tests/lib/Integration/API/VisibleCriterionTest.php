<?php

declare(strict_types=1);

namespace Netgen\EzPlatformSearchExtra\Tests\Integration\API;

use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\ContentId;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Location\Id as LocationId;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\Visible;

class VisibleCriterionTest extends BaseTest
{
    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testFindVisibleContent(): void
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();
        /** @var $contentA \eZ\Publish\API\Repository\Values\Content\Content */
        /** @var $contentB \eZ\Publish\API\Repository\Values\Content\Content */
        [$contentA, $contentB] = $this->prepareTestFixtures();
        $this->refreshSearch($repository);

        $searchResultVisible = $searchService->findContent($this->getContentQuery(true));

        $this->assertSame(2, $searchResultVisible->totalCount);
        /** @var $content1 \eZ\Publish\API\Repository\Values\Content\Content */
        /** @var $content2 \eZ\Publish\API\Repository\Values\Content\Content */
        $content1 = $searchResultVisible->searchHits[0]->valueObject;
        $content2 = $searchResultVisible->searchHits[1]->valueObject;
        $this->assertSame($contentA->id, $content1->id);
        $this->assertSame($contentB->id, $content2->id);

        $searchResultNotVisible = $searchService->findContent($this->getContentQuery(false));

        $this->assertSame(0, $searchResultNotVisible->totalCount);
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testFindHiddenContent()
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();
        $contentService = $repository->getContentService();
        /** @var $contentA \eZ\Publish\API\Repository\Values\Content\Content */
        /** @var $contentB \eZ\Publish\API\Repository\Values\Content\Content */
        [$contentA, $contentB] = $this->prepareTestFixtures();

        $contentService->hideContent($contentA->contentInfo);
        $this->refreshSearch($repository);

        $searchResultNotVisible = $searchService->findContent($this->getContentQuery(false));

        $this->assertSame(1, $searchResultNotVisible->totalCount);
        /** @var $content1 \eZ\Publish\API\Repository\Values\Content\Content */
        $content1 = $searchResultNotVisible->searchHits[0]->valueObject;
        $this->assertSame($contentA->id, $content1->id);

        $searchResultVisible = $searchService->findContent($this->getContentQuery(true));

        $this->assertSame(1, $searchResultVisible->totalCount);
        /** @var $content2 \eZ\Publish\API\Repository\Values\Content\Content */
        $content2 = $searchResultVisible->searchHits[0]->valueObject;
        $this->assertSame($contentB->id, $content2->id);
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testFindVisibleLocation()
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();
        /** @var $contentA \eZ\Publish\API\Repository\Values\Content\Content */
        /** @var $contentB \eZ\Publish\API\Repository\Values\Content\Content */
        [$contentA, $contentB] = $this->prepareTestFixtures();
        $this->refreshSearch($repository);

        $searchResultVisible = $searchService->findLocations($this->getLocationQuery(true));

        $this->assertSame(2, $searchResultVisible->totalCount);
        /** @var $location1 \eZ\Publish\API\Repository\Values\Content\Location */
        /** @var $location2 \eZ\Publish\API\Repository\Values\Content\Location */
        $location1 = $searchResultVisible->searchHits[0]->valueObject;
        $location2 = $searchResultVisible->searchHits[1]->valueObject;
        $this->assertSame($contentA->contentInfo->mainLocationId, $location1->id);
        $this->assertSame($contentB->contentInfo->mainLocationId, $location2->id);

        $searchResultNotVisible = $searchService->findLocations($this->getLocationQuery(false));

        $this->assertSame(0, $searchResultNotVisible->totalCount);
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testFindHiddenLocation()
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();
        $locationService = $repository->getLocationService();
        /** @var $contentA \eZ\Publish\API\Repository\Values\Content\Content */
        /** @var $contentB \eZ\Publish\API\Repository\Values\Content\Content */
        [$contentA, $contentB] = $this->prepareTestFixtures();

        $locationB = $locationService->loadLocation($contentB->contentInfo->mainLocationId);
        $locationService->hideLocation($locationB);
        $this->refreshSearch($repository);

        $searchResultNotVisible = $searchService->findLocations($this->getLocationQuery(false));

        $this->assertSame(1, $searchResultNotVisible->totalCount);
        /** @var $location2 \eZ\Publish\API\Repository\Values\Content\Location */
        $location2 = $searchResultNotVisible->searchHits[0]->valueObject;
        $this->assertSame($contentB->contentInfo->mainLocationId, $location2->id);

        $searchResultVisible = $searchService->findLocations($this->getLocationQuery(true));

        $this->assertSame(1, $searchResultVisible->totalCount);
        /** @var $location1 \eZ\Publish\API\Repository\Values\Content\Location */
        $location1 = $searchResultVisible->searchHits[0]->valueObject;
        $this->assertSame($contentA->contentInfo->mainLocationId, $location1->id);
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testFindInvisibleLocation()
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();
        $locationService = $repository->getLocationService();
        /** @var $contentA \eZ\Publish\API\Repository\Values\Content\Content */
        /** @var $contentB \eZ\Publish\API\Repository\Values\Content\Content */
        [$contentA, $contentB] = $this->prepareTestFixtures();

        $locationA = $locationService->loadLocation($contentA->contentInfo->mainLocationId);
        $locationService->hideLocation($locationA);
        $this->refreshSearch($repository);

        $searchResultNotVisible = $searchService->findLocations($this->getLocationQuery(false));

        $this->assertSame(2, $searchResultNotVisible->totalCount);
        /** @var $location1 \eZ\Publish\API\Repository\Values\Content\Location */
        /** @var $location2 \eZ\Publish\API\Repository\Values\Content\Location */
        $location1 = $searchResultNotVisible->searchHits[0]->valueObject;
        $location2 = $searchResultNotVisible->searchHits[1]->valueObject;
        $this->assertSame($contentA->contentInfo->mainLocationId, $location1->id);
        $this->assertSame($contentB->contentInfo->mainLocationId, $location2->id);

        $searchResultVisible = $searchService->findLocations($this->getLocationQuery(true));

        $this->assertSame(0, $searchResultVisible->totalCount);
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testFindLocationHiddenContent()
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();
        $contentService = $repository->getContentService();
        /** @var $contentA \eZ\Publish\API\Repository\Values\Content\Content */
        /** @var $contentB \eZ\Publish\API\Repository\Values\Content\Content */
        [$contentA, $contentB] = $this->prepareTestFixtures();

        $contentService->hideContent($contentB->contentInfo);
        $this->refreshSearch($repository);

        $searchResultNotVisible = $searchService->findLocations($this->getLocationQuery(false));

        $this->assertSame(1, $searchResultNotVisible->totalCount);
        /** @var $location2 \eZ\Publish\API\Repository\Values\Content\Location */
        $location2 = $searchResultNotVisible->searchHits[0]->valueObject;
        $this->assertSame($contentB->contentInfo->mainLocationId, $location2->id);

        $searchResultVisible = $searchService->findLocations($this->getLocationQuery(true));

        $this->assertSame(1, $searchResultVisible->totalCount);
        /** @var $location1 \eZ\Publish\API\Repository\Values\Content\Location */
        $location1 = $searchResultVisible->searchHits[0]->valueObject;
        $this->assertSame($contentA->contentInfo->mainLocationId, $location1->id);
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testFindLocationHiddenParentContent()
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();
        $contentService = $repository->getContentService();
        /** @var $contentA \eZ\Publish\API\Repository\Values\Content\Content */
        /** @var $contentB \eZ\Publish\API\Repository\Values\Content\Content */
        [$contentA, $contentB] = $this->prepareTestFixtures();

        $contentService->hideContent($contentA->contentInfo);
        $this->refreshSearch($repository);

        $searchResultNotVisible = $searchService->findLocations($this->getLocationQuery(false));

        $this->assertSame(2, $searchResultNotVisible->totalCount);
        /** @var $location1 \eZ\Publish\API\Repository\Values\Content\Location */
        /** @var $location2 \eZ\Publish\API\Repository\Values\Content\Location */
        $location1 = $searchResultNotVisible->searchHits[0]->valueObject;
        $location2 = $searchResultNotVisible->searchHits[1]->valueObject;
        $this->assertSame($contentA->contentInfo->mainLocationId, $location1->id);
        $this->assertSame($contentB->contentInfo->mainLocationId, $location2->id);

        $searchResultVisible = $searchService->findLocations($this->getLocationQuery(true));

        $this->assertSame(0, $searchResultVisible->totalCount);
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testFindLocationHiddenParentContentReveal()
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();
        $contentService = $repository->getContentService();
        /** @var $contentA \eZ\Publish\API\Repository\Values\Content\Content */
        /** @var $contentB \eZ\Publish\API\Repository\Values\Content\Content */
        [$contentA, $contentB] = $this->prepareTestFixtures();

        $contentService->hideContent($contentA->contentInfo);
        $contentService->revealContent($contentA->contentInfo);
        $this->refreshSearch($repository);

        $searchResultNotVisible = $searchService->findLocations($this->getLocationQuery(false));

        $this->assertSame(0, $searchResultNotVisible->totalCount);

        $searchResultVisible = $searchService->findLocations($this->getLocationQuery(true));

        $this->assertSame(2, $searchResultVisible->totalCount);
        /** @var $location1 \eZ\Publish\API\Repository\Values\Content\Location */
        /** @var $location2 \eZ\Publish\API\Repository\Values\Content\Location */
        $location1 = $searchResultVisible->searchHits[0]->valueObject;
        $location2 = $searchResultVisible->searchHits[1]->valueObject;
        $this->assertSame($contentA->contentInfo->mainLocationId, $location1->id);
        $this->assertSame($contentB->contentInfo->mainLocationId, $location2->id);
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testFindInvisibleParentLocationHiddenParentContentReveal()
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();
        /** @var $contentA \eZ\Publish\API\Repository\Values\Content\Content */
        /** @var $contentB \eZ\Publish\API\Repository\Values\Content\Content */
        [$contentA, $contentB] = $this->prepareTestFixtures();

        $locationA = $locationService->loadLocation($contentA->contentInfo->mainLocationId);
        $locationService->hideLocation($locationA);
        $contentService->hideContent($contentA->contentInfo);
        $contentService->revealContent($contentA->contentInfo);
        $this->refreshSearch($repository);

        $searchResultNotVisible = $searchService->findLocations($this->getLocationQuery(false));

        $this->assertSame(2, $searchResultNotVisible->totalCount);
        /** @var $location1 \eZ\Publish\API\Repository\Values\Content\Location */
        /** @var $location2 \eZ\Publish\API\Repository\Values\Content\Location */
        $location1 = $searchResultNotVisible->searchHits[0]->valueObject;
        $location2 = $searchResultNotVisible->searchHits[1]->valueObject;
        $this->assertSame($contentA->contentInfo->mainLocationId, $location1->id);
        $this->assertSame($contentB->contentInfo->mainLocationId, $location2->id);

        $searchResultVisible = $searchService->findLocations($this->getLocationQuery(true));

        $this->assertSame(0, $searchResultVisible->totalCount);
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testFindInvisibleChildLocationHiddenParentContentReveal()
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();
        /** @var $contentA \eZ\Publish\API\Repository\Values\Content\Content */
        /** @var $contentB \eZ\Publish\API\Repository\Values\Content\Content */
        [$contentA, $contentB] = $this->prepareTestFixtures();

        $locationB = $locationService->loadLocation($contentB->contentInfo->mainLocationId);
        $locationService->hideLocation($locationB);
        $contentService->hideContent($contentA->contentInfo);
        $contentService->revealContent($contentA->contentInfo);
        $this->refreshSearch($repository);

        $searchResultNotVisible = $searchService->findLocations($this->getLocationQuery(false));

        $this->assertSame(1, $searchResultNotVisible->totalCount);
        /** @var $location2 \eZ\Publish\API\Repository\Values\Content\Location */
        $location2 = $searchResultNotVisible->searchHits[0]->valueObject;
        $this->assertSame($contentB->contentInfo->mainLocationId, $location2->id);

        $searchResultVisible = $searchService->findLocations($this->getLocationQuery(true));

        $this->assertSame(1, $searchResultVisible->totalCount);
        /** @var $location1 \eZ\Publish\API\Repository\Values\Content\Location */
        $location1 = $searchResultVisible->searchHits[0]->valueObject;
        $this->assertSame($contentA->contentInfo->mainLocationId, $location1->id);
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testFindVisibleChildAdditionalLocationHiddenParentContent()
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();
        /** @var $contentA \eZ\Publish\API\Repository\Values\Content\Content */
        /** @var $contentB \eZ\Publish\API\Repository\Values\Content\Content */
        [$contentA, $contentB] = $this->prepareTestFixtures();

        $locationCreateStruct = $locationService->newLocationCreateStruct(2);
        $additionalLocation = $locationService->createLocation($contentB->contentInfo, $locationCreateStruct);
        $contentService->hideContent($contentA->contentInfo);
        $this->refreshSearch($repository);

        $searchResultNotVisible = $searchService->findLocations($this->getLocationQuery(false));

        $this->assertSame(2, $searchResultNotVisible->totalCount);
        /** @var $location1 \eZ\Publish\API\Repository\Values\Content\Location */
        /** @var $location2 \eZ\Publish\API\Repository\Values\Content\Location */
        $location1 = $searchResultNotVisible->searchHits[0]->valueObject;
        $location2 = $searchResultNotVisible->searchHits[1]->valueObject;
        $this->assertSame($contentA->contentInfo->mainLocationId, $location1->id);
        $this->assertSame($contentB->contentInfo->mainLocationId, $location2->id);

        $searchResultVisible = $searchService->findLocations($this->getLocationQuery(true));

        $this->assertSame(1, $searchResultVisible->totalCount);
        /** @var $location3 \eZ\Publish\API\Repository\Values\Content\Location */
        $location3 = $searchResultVisible->searchHits[0]->valueObject;
        $this->assertSame($additionalLocation->id, $location3->id);
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testFindVisibleChildAdditionalLocationHiddenParentContentVariant()
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();
        /** @var $contentA \eZ\Publish\API\Repository\Values\Content\Content */
        /** @var $contentB \eZ\Publish\API\Repository\Values\Content\Content */
        [$contentA, $contentB] = $this->prepareTestFixtures();

        $contentService->hideContent($contentA->contentInfo);
        $locationCreateStruct = $locationService->newLocationCreateStruct(2);
        $additionalLocation = $locationService->createLocation($contentB->contentInfo, $locationCreateStruct);
        $this->refreshSearch($repository);

        $searchResultNotVisible = $searchService->findLocations($this->getLocationQuery(false));

        $this->assertSame(2, $searchResultNotVisible->totalCount);
        /** @var $location1 \eZ\Publish\API\Repository\Values\Content\Location */
        /** @var $location2 \eZ\Publish\API\Repository\Values\Content\Location */
        $location1 = $searchResultNotVisible->searchHits[0]->valueObject;
        $location2 = $searchResultNotVisible->searchHits[1]->valueObject;
        $this->assertSame($contentA->contentInfo->mainLocationId, $location1->id);
        $this->assertSame($contentB->contentInfo->mainLocationId, $location2->id);

        $searchResultVisible = $searchService->findLocations($this->getLocationQuery(true));

        $this->assertSame(1, $searchResultVisible->totalCount);
        /** @var $location3 \eZ\Publish\API\Repository\Values\Content\Location */
        $location3 = $searchResultVisible->searchHits[0]->valueObject;
        $this->assertSame($additionalLocation->id, $location3->id);
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testFindInvisibleChildAdditionalLocationHiddenChildContent()
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();
        /** @var $contentA \eZ\Publish\API\Repository\Values\Content\Content */
        /** @var $contentB \eZ\Publish\API\Repository\Values\Content\Content */
        [$contentA, $contentB] = $this->prepareTestFixtures();

        $locationCreateStruct = $locationService->newLocationCreateStruct(2);
        $additionalLocation = $locationService->createLocation($contentB->contentInfo, $locationCreateStruct);
        $contentService->hideContent($contentB->contentInfo);
        $this->refreshSearch($repository);

        $searchResultNotVisible = $searchService->findLocations($this->getLocationQuery(false));

        $this->assertSame(2, $searchResultNotVisible->totalCount);
        /** @var $location2 \eZ\Publish\API\Repository\Values\Content\Location */
        /** @var $location3 \eZ\Publish\API\Repository\Values\Content\Location */
        $location2 = $searchResultNotVisible->searchHits[0]->valueObject;
        $location3 = $searchResultNotVisible->searchHits[1]->valueObject;
        $this->assertSame($contentB->contentInfo->mainLocationId, $location2->id);
        $this->assertSame($additionalLocation->id, $location3->id);

        $searchResultVisible = $searchService->findLocations($this->getLocationQuery(true));

        $this->assertSame(1, $searchResultVisible->totalCount);
        /** @var $location1 \eZ\Publish\API\Repository\Values\Content\Location */
        $location1 = $searchResultVisible->searchHits[0]->valueObject;
        $this->assertSame($contentA->contentInfo->mainLocationId, $location1->id);
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testFindInvisibleChildAdditionalLocationHiddenChildContentVariant()
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();
        /** @var $contentA \eZ\Publish\API\Repository\Values\Content\Content */
        /** @var $contentB \eZ\Publish\API\Repository\Values\Content\Content */
        [$contentA, $contentB] = $this->prepareTestFixtures();

        $contentService->hideContent($contentB->contentInfo);
        $locationCreateStruct = $locationService->newLocationCreateStruct(2);
        $additionalLocation = $locationService->createLocation($contentB->contentInfo, $locationCreateStruct);
        $this->refreshSearch($repository);

        $searchResultNotVisible = $searchService->findLocations($this->getLocationQuery(false));

        $this->assertSame(2, $searchResultNotVisible->totalCount);
        /** @var $location2 \eZ\Publish\API\Repository\Values\Content\Location */
        /** @var $location3 \eZ\Publish\API\Repository\Values\Content\Location */
        $location2 = $searchResultNotVisible->searchHits[0]->valueObject;
        $location3 = $searchResultNotVisible->searchHits[1]->valueObject;
        $this->assertSame($contentB->contentInfo->mainLocationId, $location2->id);
        $this->assertSame($additionalLocation->id, $location3->id);

        $searchResultVisible = $searchService->findLocations($this->getLocationQuery(true));

        $this->assertSame(1, $searchResultVisible->totalCount);
        /** @var $location1 \eZ\Publish\API\Repository\Values\Content\Location */
        $location1 = $searchResultVisible->searchHits[0]->valueObject;
        $this->assertSame($contentA->contentInfo->mainLocationId, $location1->id);
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testFindInvisibleChildAdditionalSubtreeHiddenChildContent()
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();
        /** @var $contentA \eZ\Publish\API\Repository\Values\Content\Content */
        /** @var $contentB \eZ\Publish\API\Repository\Values\Content\Content */
        [$contentA, $contentB] = $this->prepareTestFixtures();

        $contentService->hideContent($contentB->contentInfo);
        $locationCreateStruct = $locationService->newLocationCreateStruct(2);
        $additionalLocation1 = $locationService->createLocation($contentB->contentInfo, $locationCreateStruct);
        $locationCreateStruct = $locationService->newLocationCreateStruct($additionalLocation1->id);
        $additionalLocation2 = $locationService->createLocation($contentA->contentInfo, $locationCreateStruct);
        $this->refreshSearch($repository);

        $searchResultNotVisible = $searchService->findLocations($this->getLocationQuery(false));

        $this->assertSame(3, $searchResultNotVisible->totalCount);
        /** @var $location2 \eZ\Publish\API\Repository\Values\Content\Location */
        /** @var $location3 \eZ\Publish\API\Repository\Values\Content\Location */
        /** @var $location4 \eZ\Publish\API\Repository\Values\Content\Location */
        $location2 = $searchResultNotVisible->searchHits[0]->valueObject;
        $location3 = $searchResultNotVisible->searchHits[1]->valueObject;
        $location4 = $searchResultNotVisible->searchHits[2]->valueObject;
        $this->assertSame($contentB->contentInfo->mainLocationId, $location2->id);
        $this->assertSame($additionalLocation1->id, $location3->id);
        $this->assertSame($additionalLocation2->id, $location4->id);

        $searchResultVisible = $searchService->findLocations($this->getLocationQuery(true));

        $this->assertSame(1, $searchResultVisible->totalCount);
        /** @var $location1 \eZ\Publish\API\Repository\Values\Content\Location */
        $location1 = $searchResultVisible->searchHits[0]->valueObject;
        $this->assertSame($contentA->contentInfo->mainLocationId, $location1->id);
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testFindInvisibleChildAdditionalSubtreeHiddenChildContentVariant()
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();
        /** @var $contentA \eZ\Publish\API\Repository\Values\Content\Content */
        /** @var $contentB \eZ\Publish\API\Repository\Values\Content\Content */
        [$contentA, $contentB] = $this->prepareTestFixtures();

        $locationCreateStruct = $locationService->newLocationCreateStruct(2);
        $additionalLocation1 = $locationService->createLocation($contentB->contentInfo, $locationCreateStruct);
        $locationCreateStruct = $locationService->newLocationCreateStruct($additionalLocation1->id);
        $additionalLocation2 = $locationService->createLocation($contentA->contentInfo, $locationCreateStruct);
        $contentService->hideContent($contentB->contentInfo);
        $this->refreshSearch($repository);

        $searchResultNotVisible = $searchService->findLocations($this->getLocationQuery(false));

        $this->assertSame(3, $searchResultNotVisible->totalCount);
        /** @var $location2 \eZ\Publish\API\Repository\Values\Content\Location */
        /** @var $location3 \eZ\Publish\API\Repository\Values\Content\Location */
        /** @var $location4 \eZ\Publish\API\Repository\Values\Content\Location */
        $location2 = $searchResultNotVisible->searchHits[0]->valueObject;
        $location3 = $searchResultNotVisible->searchHits[1]->valueObject;
        $location4 = $searchResultNotVisible->searchHits[2]->valueObject;
        $this->assertSame($contentB->contentInfo->mainLocationId, $location2->id);
        $this->assertSame($additionalLocation1->id, $location3->id);
        $this->assertSame($additionalLocation2->id, $location4->id);

        $searchResultVisible = $searchService->findLocations($this->getLocationQuery(true));

        $this->assertSame(1, $searchResultVisible->totalCount);
        /** @var $location1 \eZ\Publish\API\Repository\Values\Content\Location */
        $location1 = $searchResultVisible->searchHits[0]->valueObject;
        $this->assertSame($contentA->contentInfo->mainLocationId, $location1->id);
    }

    protected function getContentQuery(bool $visible): Query
    {
        return new Query([
            'filter' => new LogicalAnd([
                new ContentTypeIdentifier('stump'),
                new Visible($visible),
            ]),
            'sortClauses' => [
                new ContentId(Query::SORT_ASC),
            ],
        ]);
    }

    protected function getLocationQuery(bool $visible): LocationQuery
    {
        return new LocationQuery([
            'filter' => new LogicalAnd([
                new ContentTypeIdentifier('stump'),
                new Visible($visible),
            ]),
            'sortClauses' => [
                new LocationId(Query::SORT_ASC),
            ],
        ]);
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function prepareTestFixtures()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();
        $locationService = $repository->getLocationService();

        $contentTypeGroups = $contentTypeService->loadContentTypeGroups();
        $contentTypeCreateStruct = $contentTypeService->newContentTypeCreateStruct('stump');
        $contentTypeCreateStruct->mainLanguageCode = 'eng-GB';
        $contentTypeCreateStruct->names = ['eng-GB' => 'Stump type'];
        $fieldDefinitionCreateStruct = $contentTypeService->newFieldDefinitionCreateStruct('width', 'ezinteger');
        $contentTypeCreateStruct->addFieldDefinition($fieldDefinitionCreateStruct);
        $contentTypeDraft = $contentTypeService->createContentType($contentTypeCreateStruct, [reset($contentTypeGroups)]);
        $contentTypeService->publishContentTypeDraft($contentTypeDraft);
        $contentType = $contentTypeService->loadContentTypeByIdentifier('stump');

        $locationCreateStruct = $locationService->newLocationCreateStruct(2);

        $contentCreateStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $contentCreateStruct->setField('width', 135);
        $contentDraft = $contentService->createContent($contentCreateStruct, [$locationCreateStruct]);
        $contentA = $contentService->publishVersion($contentDraft->versionInfo);

        $mainLocation = $contentA->contentInfo->getMainLocation();
        $locationCreateStruct = $locationService->newLocationCreateStruct($mainLocation->id);

        $contentCreateStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $contentCreateStruct->setField('width', 235);
        $contentDraft = $contentService->createContent($contentCreateStruct, [$locationCreateStruct]);
        $contentB = $contentService->publishVersion($contentDraft->versionInfo);

        return [$contentA, $contentB];
    }
}
