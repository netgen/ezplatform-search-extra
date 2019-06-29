<?php

namespace Netgen\EzPlatformSearchExtra\Core\Pagination\Pagerfanta;

use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\SPI\Search\Handler as SearchHandlerInterface;

class SearchHandlerAdapter extends BaseAdapter
{
    /**
     * @var SearchHandlerInterface
     */
    private $searchHandler;

    public function __construct(Query $query, SearchHandlerInterface $searchHandler)
    {
        parent::__construct($query);

        $this->searchHandler = $searchHandler;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    protected function executeQuery(Query $query)
    {
        if ($query instanceof LocationQuery) {
            return $this->searchHandler->findLocations($query);
        }

        return $this->searchHandler->findContent($query);
    }
}
