<?php

namespace Netgen\EzPlatformSearchExtra\Tests\Integration\Implementation\Solr\SubdocumentMapper;

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType;
use eZ\Publish\SPI\Search\Document;
use Netgen\EzPlatformSearchExtra\Core\Search\Solr\SubdocumentMapper\ContentSubdocumentMapper;

/**
 * Note: here we are only simulating indexing children data.
 */
class TestContentSubdocumentMapper extends ContentSubdocumentMapper
{
    static private $dataMap = [
        // Administrator Users
        '12' => [
            0 => [
                'visible' => true,
                'price' => 40,
            ],
            1 => [
                'visible' => false,
                'price' => 50,
            ],
        ],
        // Anonymous Users
        '42' => [
            0 => [
                'visible' => true,
                'price' => 60,
            ],
            1 => [
                'visible' => false,
                'price' => 50,
            ],
        ],
    ];

    public function accept(Content $content)
    {
        return array_key_exists($content->versionInfo->contentInfo->id, static::$dataMap);
    }

    public function mapDocuments(Content $content)
    {
        return [
            new Document([
                'id' => uniqid('test_content_subdocument_', false),
                'fields' => [
                    new Field(
                        'document_type',
                        'test_content_subdocument',
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
                'id' => uniqid('test_content_subdocument_', false),
                'fields' => [
                    new Field(
                        'document_type',
                        'test_content_subdocument',
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
