<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr;

use eZ\Publish\SPI\Persistence\Content;
use EzSystems\EzPlatformSolrSearchEngine\DocumentMapper as DocumentMapperInterface;
use Netgen\EzPlatformSearchExtra\Core\Search\Solr\SubdocumentMapper\ContentSubdocumentMapper;
use Netgen\EzPlatformSearchExtra\Core\Search\Solr\SubdocumentMapper\ContentTranslationSubdocumentMapper;

/**
 * This DocumentMapper implementation adds support for indexing custom Content subdocuments.
 *
 * @see \EzSystems\EzPlatformSolrSearchEngine\DocumentMapper
 * @see \Netgen\EzPlatformSearchExtra\Core\Search\Solr\SubdocumentMapper\ContentSubdocumentMapper
 * @see \Netgen\EzPlatformSearchExtra\Core\Search\Solr\SubdocumentMapper\ContentTranslationSubdocumentMapper
 */
final class DocumentMapper implements DocumentMapperInterface
{
    /**
     * @var \EzSystems\EzPlatformSolrSearchEngine\DocumentMapper
     */
    private $nativeDocumentMapper;

    /**
     * @var \Netgen\EzPlatformSearchExtra\Core\Search\Solr\SubdocumentMapper\ContentSubdocumentMapper
     */
    private $contentSubdocumentMapper;

    /**
     * @var \Netgen\EzPlatformSearchExtra\Core\Search\Solr\SubdocumentMapper\ContentTranslationSubdocumentMapper
     */
    private $contentTranslationSubdocumentMapper;

    /**
     * @param \EzSystems\EzPlatformSolrSearchEngine\DocumentMapper $nativeDocumentMapper
     * @param \Netgen\EzPlatformSearchExtra\Core\Search\Solr\SubdocumentMapper\ContentSubdocumentMapper $contentSubdocumentMapper
     * @param \Netgen\EzPlatformSearchExtra\Core\Search\Solr\SubdocumentMapper\ContentTranslationSubdocumentMapper $contentTranslationSubdocumentMapper
     */
    public function __construct(
        DocumentMapperInterface $nativeDocumentMapper,
        ContentSubdocumentMapper $contentSubdocumentMapper,
        ContentTranslationSubdocumentMapper $contentTranslationSubdocumentMapper
    ) {
        $this->nativeDocumentMapper = $nativeDocumentMapper;
        $this->contentSubdocumentMapper = $contentSubdocumentMapper;
        $this->contentTranslationSubdocumentMapper = $contentTranslationSubdocumentMapper;
    }

    public function mapContentBlock(Content $content)
    {
        $block = $this->nativeDocumentMapper->mapContentBlock($content);
        $this->escapeDocumentIds($block);
        $subdocuments = $this->getContentSubdocuments($content);

        foreach ($block as $contentDocument) {
            $translationSubdocuments = $this->getContentTranslationSubdocuments($content, $contentDocument->languageCode);

            $contentDocument->documents = array_merge(
                $contentDocument->documents,
                $subdocuments,
                $translationSubdocuments
            );
        }

        return $block;
    }

    /**
     * @param \eZ\Publish\SPI\Search\Document[] $documents
     */
    private function escapeDocumentIds(array $documents)
    {
        foreach ($documents as $document) {
            $document->id = preg_replace('([^A-Za-z0-9/]+)', '', $document->id);

            $this->escapeDocumentIds($document->documents);
        }
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content $content
     *
     * @return array|\eZ\Publish\SPI\Search\Document[]
     */
    private function getContentSubdocuments(Content $content)
    {
        if ($this->contentSubdocumentMapper->accept($content)) {
            return $this->contentSubdocumentMapper->mapDocuments($content);
        }

        return [];
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content $content
     * @param string $languageCode
     *
     * @return array|\eZ\Publish\SPI\Search\Document[]
     */
    private function getContentTranslationSubdocuments(Content $content, $languageCode)
    {
        if ($this->contentTranslationSubdocumentMapper->accept($content, $languageCode)) {
            return $this->contentTranslationSubdocumentMapper->mapDocuments($content, $languageCode);
        }

        return [];
    }

    public function generateContentDocumentId($contentId, $languageCode = null)
    {
        return $this->nativeDocumentMapper->generateContentDocumentId($contentId, $languageCode);
    }

    public function generateLocationDocumentId($locationId, $languageCode = null)
    {
        return $this->nativeDocumentMapper->generateLocationDocumentId($locationId, $languageCode);
    }
}
