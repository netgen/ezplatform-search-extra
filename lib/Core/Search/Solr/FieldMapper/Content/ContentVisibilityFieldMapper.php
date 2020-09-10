<?php

declare(strict_types=1);

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\FieldMapper\Content;

use eZ\Publish\SPI\Persistence\Content as SPIContent;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType;
use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentFieldMapper;

class ContentVisibilityFieldMapper extends ContentFieldMapper
{
    public function accept(SPIContent $content)
    {
        return true;
    }

    public function mapFields(SPIContent $content)
    {
        return [
            new Field(
                'ng_content_visible',
                !$content->versionInfo->contentInfo->isHidden,
                new FieldType\BooleanField()
            ),
        ];
    }
}
