<?php

namespace Netgen\EzPlatformSearchExtra\Core\Pagination\Pagerfanta;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;

class SearchAdapter extends BaseAdapter
{
    /**
     * @var \eZ\Publish\API\Repository\SearchService
     */
    private $searchService;

    public function __construct(Query $query, SearchService $searchService)
    {
        parent::__construct($query);

        $this->searchService = $searchService;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    protected function executeQuery(Query $query)
    {
        if ($query instanceof LocationQuery) {
            return $this->searchService->findLocations($query);
        }

        return $this->searchService->findContent($query);
    }
}
