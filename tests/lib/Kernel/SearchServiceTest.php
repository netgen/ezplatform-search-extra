<?php

namespace Netgen\EzPlatformSearchExtra\Tests\Kernel;

use eZ\Publish\API\Repository\Tests\SearchServiceTest as KernelSearchServiceTest;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult as KernelSearchResult;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Search\SearchResult;

class SearchServiceTest extends KernelSearchServiceTest
{
    /**
     * Assert that query result matches the given fixture.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     * @param string $fixture
     * @param null|callable $closure
     * @param bool $info
     * @param bool $ignoreScore
     * @param bool $id
     */
    protected function assertQueryFixture(Query $query, $fixture, $closure = null, $ignoreScore = true, $info = false, $id = true)
    {
        $newClosure = function (&$data) use ($closure) {
            if ($data instanceof SearchResult) {
                $data = $this->mapToKernelSearchResult($data);
            }

            if (\is_callable($closure)) {
                $closure($data);
            }
        };

        return parent::assertQueryFixture($query, $fixture, $newClosure, $ignoreScore, $info, $id);
    }

    private function mapToKernelSearchResult(SearchResult $data)
    {
        return new KernelSearchResult([
            'facets' => $data->facets,
            'searchHits' => $data->searchHits,
            'spellSuggestion' => $data->spellSuggestion,
            'time' => $data->time,
            'timedOut' => $data->timedOut,
            'maxScore' => $data->maxScore,
            'totalCount' => $data->totalCount,
        ]);
    }
}
