<?php

namespace Netgen\EzPlatformSearchExtra\Tests\Integration\Implementation\Solr\FieldMapper;

use eZ\Publish\SPI\Persistence\Content as SPIContent;
use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentFieldMapper;

class TestContentFieldMapper extends ContentFieldMapper implements TestFieldMapperInterface
{
    use TestFieldMapperTrait;

    /**
     * {@inheritdoc}
     */
    public function accept(SPIContent $content)
    {
        return $this->accepts($content);
    }


    /**
     * {@inheritdoc}
     */
    public function mapFields(SPIContent $content)
    {
        return $this->getFields($content);
    }
}
