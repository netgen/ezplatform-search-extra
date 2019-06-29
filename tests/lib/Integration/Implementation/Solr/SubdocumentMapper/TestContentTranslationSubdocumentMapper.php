<?php

namespace Netgen\EzPlatformSearchExtra\Tests\Integration\Implementation\Solr\SubdocumentMapper;

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType;
use eZ\Publish\SPI\Search\Document;
use Netgen\EzPlatformSearchExtra\Core\Search\Solr\SubdocumentMapper\ContentTranslationSubdocumentMapper;

class TestContentTranslationSubdocumentMapper extends ContentTranslationSubdocumentMapper
{
    static private $dataMap = [
        // Users
        '4' => [
            0 => [
                'visible' => true,
                'price' => 60,
            ],
            1 => [
                'visible' => false,
                'price' => 50,
            ],
        ],
        // Partners
        '59' => [
            0 => [
                'visible' => true,
                'price' => 40,
            ],
            1 => [
                'visible' => false,
                'price' => 50,
            ],
        ],
    ];

    public function accept(Content $content, $languageCode)
    {
        return $languageCode === 'ger-DE' && array_key_exists($content->versionInfo->contentInfo->id, static::$dataMap);
    }

    public function mapDocuments(Content $content, $languageCode)
    {
        return [
            new Document([
                'id' => uniqid('test_content_translation_subdocument_', false),
                'fields' => [
                    new Field(
                        'document_type',
                        'test_content_translation_subdocument',
                        new FieldType\IdentifierField()
                    ),
                    new Field(
                        'visible',
                        static::$dataMap[$content->versionInfo->contentInfo->id][0]['visible'],
                        new FieldType\BooleanField()
                    ),
                    new Field(
                        'price',
                        static::$dataMap[$content->versionInfo->contentInfo->id][0]['price'],
                        new FieldType\IntegerField()
                    ),
                ],
            ]),
            new Document([
                'id' => uniqid('test_content_translation_subdocument_', false),
                'fields' => [
                    new Field(
                        'document_type',
                        'test_content_translation_subdocument',
                        new FieldType\IdentifierField()
                    ),
                    new Field(
                        'visible',
                        static::$dataMap[$content->versionInfo->contentInfo->id][1]['visible'],
                        new FieldType\BooleanField()
                    ),
                    new Field(
                        'price',
                        static::$dataMap[$content->versionInfo->contentInfo->id][1]['price'],
                        new FieldType\IntegerField()
                    ),
                ],
            ]),
        ];
    }
}
