<?php

declare(strict_types=1);

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\FieldMapper\ContentTranslation;

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType\StringField;
use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentTranslationFieldMapper;

class ContentNameFieldMapper extends ContentTranslationFieldMapper
{
    public function accept(Content $content, $languageCode): bool
    {
        return true;
    }

    public function mapFields(Content $content, $languageCode): array
    {
        if (!isset($content->versionInfo->names[$languageCode])) {
            return [];
        }

        return [
            new Field(
                'ng_content_name',
                $content->versionInfo->names[$languageCode],
                new StringField()
            ),
        ];
    }
}
