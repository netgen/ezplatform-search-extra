<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr;

use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use EzSystems\EzPlatformSolrSearchEngine\DocumentMapper;
use EzSystems\EzPlatformSolrSearchEngine\Handler as BaseHandler;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;

class Handler extends BaseHandler
{
    public function findContent(Query $query, array $languageFilter = array())
    {
        $query = clone $query;
        $query->filter = $query->filter ?: new Criterion\MatchAll();
        $query->query = $query->query ?: new Criterion\MatchAll();

        $this->coreFilter->apply(
            $query,
            $languageFilter,
            DocumentMapper::DOCUMENT_TYPE_IDENTIFIER_CONTENT
        );

        return $this->resultExtractor->extract(
            $this->gateway->findContent($query, $languageFilter),
            $query->facetBuilders,
            $query
        );
    }

    public function findLocations(LocationQuery $query, array $languageFilter = array())
    {
        $query = clone $query;
        $query->query = $query->query ?: new Criterion\MatchAll();

        $this->coreFilter->apply(
            $query,
            $languageFilter,
            DocumentMapper::DOCUMENT_TYPE_IDENTIFIER_LOCATION
        );

        return $this->resultExtractor->extract(
            $this->gateway->findLocations($query, $languageFilter),
            $query->facetBuilders,
            $query
        );
    }

    protected function deleteAllItemsWithoutAdditionalLocation($locationId)
    {
        $query = $this->prepareQuery();
        $query->filter = new Criterion\LogicalAnd([
            $this->allItemsWithinLocation($locationId),
            new Criterion\LogicalNot($this->allItemsWithinLocationWithAdditionalLocation($locationId)),
        ]);

        $contentIds = $this->extractContentIds(
            $this->gateway->searchAllEndpoints($query)
        );

        foreach ($contentIds as $contentId) {
            $idPrefix = $this->mapper->generateContentDocumentId($contentId);
            $this->gateway->deleteByQuery("_root_:{$idPrefix}*");
        }
    }

    protected function updateAllElementsWithAdditionalLocation($locationId)
    {
        $query = $this->prepareQuery();
        $query->filter = new Criterion\LogicalAnd([
            $this->allItemsWithinLocation($locationId),
            $this->allItemsWithinLocationWithAdditionalLocation($locationId),
        ]);

        $contentIds = $this->extractContentIds(
            $this->gateway->searchAllEndpoints($query)
        );

        $contentItems = [];
        foreach ($contentIds as $contentId) {
            try {
                $contentInfo = $this->contentHandler->loadContentInfo($contentId);
            } catch (NotFoundException $e) {
                continue;
            }

            $contentItems[] = $this->contentHandler->load(
                $contentInfo->id,
                $contentInfo->currentVersionNo
            );
        }

        $this->bulkIndexContent($contentItems);
    }

    /**
     * @param mixed $data
     *
     * @return array
     */
    private function extractContentIds($data)
    {
        $ids = [];

        foreach ($data->response->docs as $doc) {
            $ids[] = $doc->content_id_id;
        }

        return $ids;
    }
}
