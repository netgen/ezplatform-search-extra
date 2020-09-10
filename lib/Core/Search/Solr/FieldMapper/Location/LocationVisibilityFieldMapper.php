<?php

declare(strict_types=1);

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\FieldMapper\Location;

use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandler;
use eZ\Publish\SPI\Persistence\Content\Location as SPILocation;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType;
use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\LocationFieldMapper;

class LocationVisibilityFieldMapper extends LocationFieldMapper
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Handler
     */
    protected $contentHandler;

    public function __construct(ContentHandler $contentHandler)
    {
        $this->contentHandler = $contentHandler;
    }

    public function accept(SPILocation $location)
    {
        return true;
    }

    public function mapFields(SPILocation $location)
    {
        $contentInfo = $this->contentHandler->loadContentInfo($location->contentId);

        return [
            new Field(
                'ng_location_visible',
                !$location->hidden && !$location->invisible && !$contentInfo->isHidden,
                new FieldType\BooleanField()
            ),
        ];
    }
}
