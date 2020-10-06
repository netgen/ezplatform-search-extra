<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\FieldMapper\Content;

use eZ\Publish\SPI\Persistence\Content as SPIContent;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as LocationHandler;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType\IntegerField;
use eZ\Publish\SPI\Search\FieldType\MultipleIntegerField;
use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentFieldMapper;

class ContentAndLocationIdFieldMapper extends ContentFieldMapper
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    protected $locationHandler;

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Location\Handler $locationHandler
     */
    public function __construct(LocationHandler $locationHandler)
    {
        $this->locationHandler = $locationHandler;
    }

    public function accept(SPIContent $content)
    {
        return true;
    }

    public function mapFields(SPIContent $content)
    {
        $locations = $this->locationHandler->loadLocationsByContent($content->versionInfo->contentInfo->id);
        $locationIds = [];

        foreach ($locations as $location) {
            $locationIds[] = $location->id;
        }

        return [
            new Field(
                'ng_content_id',
                $content->versionInfo->contentInfo->id,
                new IntegerField()
            ),
            new Field(
                'ng_location_id',
                $locationIds,
                new MultipleIntegerField()
            ),
        ];
    }
}
