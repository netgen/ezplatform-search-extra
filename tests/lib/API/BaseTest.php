<?php

namespace Netgen\EzPlatformSearchExtra\Tests\API;

use eZ\Publish\API\Repository\Tests\BaseTest as APIBaseTest;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use RuntimeException;

abstract class BaseTest extends APIBaseTest
{
    protected function assertSearchResultContentIds(
        SearchResult $searchResult,
        array $expectedIds,
        $totalCount = null
    ) {
        $totalCount = $totalCount ?: count($expectedIds);
        $this->assertEquals($totalCount, $searchResult->totalCount);

        $foundIds = [];

        foreach ($searchResult->searchHits as $searchHit) {
            $value = $searchHit->valueObject;

            if ($value instanceof ContentInfo) {
                $foundIds[] = $value->id;
            } elseif ($value instanceof Location) {
                $foundIds[] = $value->contentId;
            } else {
                throw new RuntimeException(
                    'Unknown value type: ' . get_class($value)
                );
            }
        }

        $this->assertEquals($expectedIds, $foundIds);
    }

    protected function getSearchService($initialInitializeFromScratch = true)
    {
        return $this->getRepository($initialInitializeFromScratch)->getSearchService();
    }
}
