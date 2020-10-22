<?php

declare(strict_types=1);

namespace Netgen\EzPlatformSearchExtra\Core\Pagination\Pagerfanta;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResultCollection;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Search\SearchResult as ExtraSearchResult;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Search\Suggestion;
use Netgen\EzPlatformSearchExtra\Core\Pagination\SearchResultExtras;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * Base search adapter.
 */
abstract class BaseAdapter implements AdapterInterface, SearchResultExtras
{
    private $query;

    /**
     * @var int
     */
    private $nbResults;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Search\Facet[]
     */
    private $facets;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Search\AggregationResultCollection
     */
    private $aggregations;

    /**
     * @var float
     */
    private $maxScore;

    /**
     * @var \Netgen\EzPlatformSearchExtra\API\Values\Content\Search\Suggestion
     */
    private $suggestion;

    /**
     * @var int
     */
    private $time;

    /**
     * @var bool
     */
    private $isExtraInfoInitialized = false;

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     */
    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    public function getNbResults(): int
    {
        $this->initializeExtraInfo();

        return $this->nbResults;
    }

    public function getFacets(): array
    {
        $this->initializeExtraInfo();

        return $this->facets;
    }

    public function getAggregations(): AggregationResultCollection
    {
        $this->initializeExtraInfo();

        return $this->aggregations;
    }

    public function getMaxScore(): float
    {
        $this->initializeExtraInfo();

        return $this->maxScore;
    }

    public function getSuggestion(): Suggestion
    {
        $this->initializeExtraInfo();

        return $this->suggestion;
    }

    public function getTime(): ?int
    {
        return $this->time;
    }

    public function getSlice($offset, $length)
    {
        $query = clone $this->query;
        $query->offset = $offset;
        $query->limit = $length;
        $query->performCount = false;

        $searchResult = $this->executeQuery($query);

        $this->time = $searchResult->time;

        if (!$this->isExtraInfoInitialized && $searchResult->totalCount !== null) {
            $this->setExtraInfo($searchResult);
        }

        return new Slice($searchResult->searchHits);
    }

    /**
     * Execute the given $query and return SearchResult instance.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    abstract protected function executeQuery(Query $query): SearchResult;

    private function initializeExtraInfo(): void
    {
        if ($this->isExtraInfoInitialized) {
            return;
        }

        $query = clone $this->query;
        $query->limit = 0;
        $searchResult = $this->executeQuery($query);

        $this->setExtraInfo($searchResult);
    }

    private function setExtraInfo(SearchResult $searchResult): void
    {
        $this->facets = $searchResult->facets;
        $this->aggregations = $searchResult->aggregations;
        $this->maxScore = $searchResult->maxScore;
        $this->nbResults = $searchResult->totalCount;
        $this->suggestion = new Suggestion([]);

        if ($searchResult instanceof ExtraSearchResult && $searchResult->suggestion instanceof Suggestion) {
            $this->suggestion = $searchResult->suggestion;
        }

        $this->isExtraInfoInitialized = true;
    }
}
