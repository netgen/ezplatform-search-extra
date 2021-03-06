<?php

declare(strict_types=1);

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\ResultExtractor;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult as APISearchResult;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\Location;
use EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointRegistry;
use EzSystems\EzPlatformSolrSearchEngine\Query\FacetFieldVisitor;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor as BaseResultExtractor;
use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandler;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as LocationHandler;
use EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Search\Suggestion;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Search\WordSuggestion;
use Netgen\EzPlatformSearchExtra\Core\Search\Solr\ResultExtractor;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Search\SearchResult;
use RuntimeException;

/**
 * The Loading Result Extractor extracts the value object from the Solr search hit data
 * by loading it from the persistence layer.
 */
final class LoadingResultExtractor Extends ResultExtractor
{
    protected $contentHandler;
    protected $locationHandler;
    private $nativeResultExtractor;

    public function __construct(
        ContentHandler $contentHandler,
        LocationHandler $locationHandler,
        BaseResultExtractor $nativeResultExtractor,
        FacetFieldVisitor $facetBuilderVisitor,
        AggregationResultExtractor $aggregationResultExtractor,
        EndpointRegistry $endpointRegistry
    ) {
        $this->contentHandler = $contentHandler;
        $this->locationHandler = $locationHandler;
        $this->nativeResultExtractor = $nativeResultExtractor;

        parent::__construct($facetBuilderVisitor, $aggregationResultExtractor, $endpointRegistry);
    }

    protected function extractSearchResult(
        $data,
        array $facetBuilders = [],
        array $aggregations = [],
        array $languageFilter = []
    ): APISearchResult {
        $searchResult = $this->nativeResultExtractor->extract(
            $data,
            $facetBuilders,
            $aggregations,
            $languageFilter
        );
        $searchResult = new SearchResult(get_object_vars($searchResult));
        $this->replaceExtractedValuesByLoadedValues($searchResult);

        if ($this->isSpellCheckAvailable($data)) {
            $searchResult->suggestion = $this->getSpellCheckSuggestion($data);
        }

        return $searchResult;
    }

    /**
     * @param \Netgen\EzPlatformSearchExtra\API\Values\Content\Search\SearchResult $searchResult
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    private function replaceExtractedValuesByLoadedValues(SearchResult $searchResult): APISearchResult
    {
        $valueObjectMapById = $this->loadValueObjectMapById($searchResult);

        foreach ($searchResult->searchHits as $index => $searchHit) {
            $id = $this->getValueObjectId($searchHit->valueObject);

            if (array_key_exists($id, $valueObjectMapById)) {
                $searchHit->valueObject = $valueObjectMapById[$id];
            } else {
                unset($searchResult->searchHits[$index]);
                --$searchResult->totalCount;
            }
        }

        return $searchResult;
    }

    /**
     * @param \Netgen\EzPlatformSearchExtra\API\Values\Content\Search\SearchResult $searchResult
     *
     * @return array|\eZ\Publish\SPI\Persistence\Content\ContentInfo[]
     */
    private function loadValueObjectMapById(SearchResult $searchResult): array
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

    private function extractIdList(SearchResult $searchResult): array
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

    private function loadContentInfoMapByIdList(array $contentIdList): array
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
    private function loadLocationMapByIdList(array $locationIdList): array
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
     * @param $data
     *
     * @return bool
     */
    private function isSpellCheckAvailable($data): bool
    {
        return property_exists($data, 'spellcheck') && property_exists($data->spellcheck, 'suggestions');
    }

    /**
     * Extracts spell check suggestions from received data.
     *
     * @param $data
     *
     * @return \Netgen\EzPlatformSearchExtra\API\Values\Content\Search\Suggestion
     */
    private function getSpellCheckSuggestion($data): Suggestion
    {
        $receivedSuggestions = (array) $data->spellcheck->suggestions;
        $wordSuggestions = [];

        for ($i = 0; $i < (count($receivedSuggestions) - 1); $i += 2) {
            $originalWord = $receivedSuggestions[$i];
            $receivedWordSuggestions = $receivedSuggestions[$i+1];

            if (!property_exists($receivedWordSuggestions, 'suggestion') || empty($receivedWordSuggestions->suggestion)) {
                continue;
            }

            foreach ($receivedWordSuggestions->suggestion as $suggestion) {
                $wordSuggestions[] = new WordSuggestion([
                    'originalWord' => (string) $originalWord,
                    'suggestedWord' => (string) $suggestion->word,
                    'frequency' => (int) $suggestion->freq,
                ]);
            }
        }

        return new Suggestion($wordSuggestions);
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
