<?php

namespace Netgen\EzPlatformSearchExtra\Tests\Kernel;

use eZ\Publish\API\Repository\Tests\SearchServiceTest as KernelSearchServiceTest;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult as KernelSearchResult;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Search\SearchResult;

class SearchServiceTest extends KernelSearchServiceTest
{
    protected function assertQueryFixture(
        Query $query,
        string $fixtureFilePath,
        ?callable $closure = null,
        bool $ignoreScore = true,
        bool $info = false,
        bool $id = true
    ): void {
        $newClosure = function (&$data) use ($closure) {
            if ($data instanceof SearchResult) {
                $data = $this->mapToKernelSearchResult($data);
            }

            if (\is_callable($closure)) {
                $closure($data);
            }
        };

        parent::assertQueryFixture($query, $fixtureFilePath, $newClosure, $ignoreScore, $info, $id);
    }

    private function mapToKernelSearchResult(SearchResult $data): KernelSearchResult
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
