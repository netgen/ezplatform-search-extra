<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\ResultExtractor;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\Location;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointRegistry;
use EzSystems\EzPlatformSolrSearchEngine\Query\FacetFieldVisitor;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor as BaseResultExtractor;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandler;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as LocationHandler;
use Netgen\EzPlatformSearchExtra\Core\Search\Solr\ResultExtractor;
use RuntimeException;

/**
 * The Loading Result Extractor extracts the value object from the Solr search hit data
 * by loading it from the persistence layer.
 */
final class LoadingResultExtractor Extends ResultExtractor
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Handler
     */
    protected $contentHandler;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    protected $locationHandler;

    /**
     * @var \EzSystems\EzPlatformSolrSearchEngine\ResultExtractor
     */
    private $nativeResultExtractor;

    public function __construct(
        ContentHandler $contentHandler,
        LocationHandler $locationHandler,
        BaseResultExtractor $nativeResultExtractor,
        FacetFieldVisitor $facetBuilderVisitor,
        EndpointRegistry $endpointRegistry
    ) {
        $this->contentHandler = $contentHandler;
        $this->locationHandler = $locationHandler;
        $this->nativeResultExtractor = $nativeResultExtractor;

        parent::__construct($facetBuilderVisitor, $endpointRegistry);
    }

    protected function extractSearchResult($data, array $facetBuilders = [])
    {
        $searchResult = $this->nativeResultExtractor->extract($data, $facetBuilders);

        $this->replaceExtractedValuesByLoadedValues($searchResult);

        return $searchResult;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Search\SearchResult $searchResult
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    private function replaceExtractedValuesByLoadedValues(SearchResult $searchResult)
    {
        $valueObjectMapById = $this->loadValueObjectMapById($searchResult);

        foreach ($searchResult->searchHits as $index => $searchHit) {
            $id = $this->getValueObjectId($searchHit->valueObject);

            if (array_key_exists($id, $valueObjectMapById)) {
                $searchHit->valueObject = $valueObjectMapById[$id];
            } else {
                //unset($searchResult->searchHits[$index]);
                //--$searchResult->totalCount;
            }
        }

        return $searchResult;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Search\SearchResult $searchResult
     *
     * @return array|\eZ\Publish\SPI\Persistence\Content\ContentInfo[]
     */
    private function loadValueObjectMapById(SearchResult $searchResult)
    {
        if (!isset($searchResult->searchHits[0])) {
            return [];
        }

        $idList = $this->extractIdList($searchResult);

        if ($searchResult->searchHits[0]->valueObject instanceof ContentInfo) {
            return $this->loadContentInfoMapByIdList($idList);
        }

        return $this->loadLocationMapByIdList($idList);
    }

    private function extractIdList(SearchResult $searchResult)
    {
        $idList = [];

        foreach ($searchResult->searchHits as $searchHit) {
            $idList[] = $this->getValueObjectId($searchHit->valueObject);
        }

        return $idList;
    }

    private function getValueObjectId($valueObject)
    {
        if ($valueObject instanceof ContentInfo) {
            return $valueObject->id;
        }

        if ($valueObject instanceof Location) {
            return $valueObject->id;
        }

        throw new RuntimeException("Couldn't handle given value object.");
    }

    private function loadContentInfoMapByIdList(array $contentIdList)
    {
        if (method_exists($this->contentHandler, 'loadContentInfoList')) {
            return $this->contentHandler->loadContentInfoList($contentIdList);
        }

        $contentInfoList = [];

        foreach ($contentIdList as $contentId) {
            try {
                $contentInfoList[$contentId] = $this->contentHandler->loadContentInfo($contentId);
            } catch (NotFoundException $e) {
                // do nothing
            }
        }

        return $contentInfoList;
    }

    /**
     * @param array $locationIdList
     *
     * @return array|\eZ\Publish\SPI\Persistence\Content\ContentInfo[]
     */
    private function loadLocationMapByIdList(array $locationIdList)
    {
        if (method_exists($this->locationHandler, 'loadList')) {
            return $this->locationHandler->loadList($locationIdList);
        }

        $locationList = [];

        foreach ($locationIdList as $locationId) {
            try {
                $locationList[$locationId] = $this->locationHandler->load($locationId);
            } catch (NotFoundException $e) {
                // do nothing
            }
        }

        return $locationList;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException If search $hit could not be handled
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function extractHit($hit)
    {
        if ($hit->document_type_id === 'content') {
            return $this->contentHandler->loadContentInfo($hit->content_id_id);
        }

        if ($hit->document_type_id === 'location') {
            return $this->locationHandler->load($hit->location_id_id);
        }

        throw new RuntimeException(
            "Extracting documents of type '{$hit->document_type_id}' is not handled."
        );
    }
}
