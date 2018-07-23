<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\SubdocumentMapper;

use eZ\Publish\SPI\Persistence\Content;

/**
 * Maps Content to an array of subdocuments.
 */
abstract class ContentSubdocumentMapper
{
    /**
     * Indicate if the mapper accepts the given $content for mapping.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     *
     * @return bool
     */
    abstract public function accept(Content $content);

    /**
     * Maps given Content to a Document.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     *
     * @return \eZ\Publish\SPI\Search\Document[]
     */
    abstract public function mapDocuments(Content $content);
}
