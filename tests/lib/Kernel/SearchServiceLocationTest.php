<?php

namespace Netgen\EzPlatformSearchExtra\Tests\Kernel;

use eZ\Publish\API\Repository\Tests\SearchServiceLocationTest as KernelSearchServiceLocationTest;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult as KernelSearchResult;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit as KernelSearchHit;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Search\SearchHit;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Search\SearchResult;

class SearchServiceLocationTest extends KernelSearchServiceLocationTest
{
    /**
     * Assert that query result matches the given fixture.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LocationQuery $query
     * @param string $fixture
     * @param null|callable $closure
     * @param bool $ignoreScore
     */
    protected function assertQueryFixture(LocationQuery $query, $fixture, $closure = null, $ignoreScore = true)
    {
        $newClosure = function (&$data) use ($closure) {
            if ($data instanceof SearchResult) {
                $data = $this->mapToKernelSearchResult($data);
            }

            if (\is_callable($closure)) {
                $closure($data);
            }
        };

        return parent::assertQueryFixture($query, $fixture, $newClosure, $ignoreScore);
    }

    private function mapToKernelSearchResult(SearchResult $data)
    {
        $kernelSearchHits = [];

        /** @var \Netgen\EzPlatformSearchExtra\API\Values\Content\Search\SearchHit $searchHit */
        foreach ($data->searchHits as $searchHit) {
            $kernelSearchHits[] = $this->mapToKernelSearchHit($searchHit);
        }

        return new KernelSearchResult([
            'facets' => $data->facets,
            'searchHits' => $kernelSearchHits,
            'spellSuggestion' => $data->spellSuggestion,
            'time' => $data->time,
            'timedOut' => $data->timedOut,
            'maxScore' => $data->maxScore,
            'totalCount' => $data->totalCount,
        ]);
    }

    private function mapToKernelSearchHit(SearchHit $searchHit)
    {
        return new KernelSearchHit([
            'valueObject' => $searchHit->valueObject,
            'score' => $searchHit->score,
            'index' => $searchHit->index,
            'matchedTranslation' => $searchHit->matchedTranslation,
            'highlight' => $searchHit->highlight,
        ]);
    }
}
