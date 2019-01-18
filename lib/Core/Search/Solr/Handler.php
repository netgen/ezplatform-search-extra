<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalNot;
use EzSystems\EzPlatformSolrSearchEngine\Handler as BaseHandler;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;

class Handler extends BaseHandler
{
    protected function deleteAllItemsWithoutAdditionalLocation($locationId)
    {
        $query = $this->prepareQuery();
        $query->filter = new LogicalAnd([
            $this->allItemsWithinLocation($locationId),
            new LogicalNot($this->allItemsWithinLocationWithAdditionalLocation($locationId)),
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
        $query->filter = new LogicalAnd([
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
