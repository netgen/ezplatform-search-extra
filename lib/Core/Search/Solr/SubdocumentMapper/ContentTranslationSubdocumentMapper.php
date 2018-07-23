<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\SubdocumentMapper;

use eZ\Publish\SPI\Persistence\Content;

/**
 * Maps Content in a specific language to an array of subdocuments.
 */
abstract class ContentTranslationSubdocumentMapper
{
    /**
     * Indicate if the mapper accepts the given $content for mapping.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     * @param string $languageCode
     *
     * @return bool
     */
    abstract public function accept(Content $content, $languageCode);

    /**
     * Maps given Content to a Document.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     * @param string $languageCode
     *
     * @return \eZ\Publish\SPI\Search\Document[]
     */
    abstract public function mapDocuments(Content $content, $languageCode);
}
