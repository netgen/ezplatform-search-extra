<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\SubdocumentMapper\ContentSubdocumentMapper;

use eZ\Publish\SPI\Persistence\Content;
use Netgen\EzPlatformSearchExtra\Core\Search\Solr\SubdocumentMapper\ContentSubdocumentMapper;

/**
 * Aggregate implementation of Content subdocument mapper.
 */
final class Aggregate extends ContentSubdocumentMapper
{
    /**
     * An array of aggregated subdocument mappers.
     *
     * @var \Netgen\EzPlatformSearchExtra\Core\Search\Solr\SubdocumentMapper\ContentSubdocumentMapper[]
     */
    protected $mappers = [];

    /**
     * @param \Netgen\EzPlatformSearchExtra\Core\Search\Solr\SubdocumentMapper\ContentSubdocumentMapper[] $mappers
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
     * @param \Netgen\EzPlatformSearchExtra\Core\Search\Solr\SubdocumentMapper\ContentSubdocumentMapper $mapper
     */
    public function addMapper(ContentSubdocumentMapper $mapper)
    {
        $this->mappers[] = $mapper;
    }

    public function accept(Content $content)
    {
        return true;
    }

    public function mapDocuments(Content $content)
    {
        $documentsGrouped = [[]];

        foreach ($this->mappers as $mapper) {
            if ($mapper->accept($content)) {
                $documentsGrouped[] = $mapper->mapDocuments($content);
            }
        }

        return array_merge(...$documentsGrouped);
    }
}
