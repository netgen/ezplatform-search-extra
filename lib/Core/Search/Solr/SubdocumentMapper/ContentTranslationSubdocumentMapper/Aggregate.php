<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\SubdocumentMapper\ContentTranslationSubdocumentMapper;

use eZ\Publish\SPI\Persistence\Content;
use Netgen\EzPlatformSearchExtra\Core\Search\Solr\SubdocumentMapper\ContentTranslationSubdocumentMapper;

/**
 * Aggregate implementation of Content translation subdocument mapper.
 */
final class Aggregate extends ContentTranslationSubdocumentMapper
{
    /**
     * An array of aggregated subdocument mappers.
     *
     * @var \Netgen\EzPlatformSearchExtra\Core\Search\Solr\SubdocumentMapper\ContentTranslationSubdocumentMapper[]
     */
    protected $mappers = [];

    /**
     * @param \Netgen\EzPlatformSearchExtra\Core\Search\Solr\SubdocumentMapper\ContentTranslationSubdocumentMapper[] $mappers
     */
    public function __construct(array $mappers = [])
    {
        foreach ($mappers as $mapper) {
            $this->addMapper($mapper);
        }
    }

    /**
     * Adds given $mapper to the internal collection.
     *
     * @param \Netgen\EzPlatformSearchExtra\Core\Search\Solr\SubdocumentMapper\ContentTranslationSubdocumentMapper $mapper
     */
    public function addMapper(ContentTranslationSubdocumentMapper $mapper)
    {
        $this->mappers[] = $mapper;
    }

    public function accept(Content $content, $languageCode)
    {
        return true;
    }

    public function mapDocuments(Content $content, $languageCode)
    {
        $documentsGrouped = [[]];

        foreach ($this->mappers as $mapper) {
            if ($mapper->accept($content, $languageCode)) {
                $documentsGrouped[] = $mapper->mapDocuments($content, $languageCode);
            }
        }

        return array_merge(...$documentsGrouped);
    }
}
