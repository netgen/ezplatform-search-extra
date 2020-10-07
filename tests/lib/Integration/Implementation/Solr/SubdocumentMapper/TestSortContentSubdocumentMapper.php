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
class TestSortContentSubdocumentMapper extends ContentSubdocumentMapper
{
    static private $dataMap = [
        // Administrator Users
        '12' => [
            0 => [
                'price' => 5,
            ],
            1 => [
                'price' => 5,
            ],
            2 => [
                'price' => 35,
            ],
        ],
        // Anonymous Users
        '42' => [
            0 => [
                'price' => 4,
            ],
            1 => [
                'price' => 500,
            ],
            2 => [
                'price' => 60,
            ]
        ],
        // Partners
        '59' => [
            0 => [
                'price' => 10,
            ],
            1 => [
                'price' => 10,
            ],
            2 => [
                'price' => 20,
            ],
            3 => [
                'price' => 30,
            ],
        ],
    ];

    public function accept(Content $content)
    {
        return array_key_exists($content->versionInfo->contentInfo->id, static::$dataMap);
    }

    public function mapDocuments(Content $content)
    {
        $documents = [];

        foreach (static::$dataMap[$content->versionInfo->contentInfo->id] as $data) {
            $documents[] = new Document([
                'id' => uniqid('test_content_subdocument_', false),
                'fields' => [
                    new Field(
                        'document_type',
                        'test_sort_content_subdocument',
                        new FieldType\IdentifierField()
                    ),
                    new Field(
                        'price',
                        $data['price'],
                        new FieldType\IntegerField()
                    ),
                ],
            ]);
        }

        return $documents;
    }
}
