<?php


namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\FieldMapper\Location;

use eZ\Publish\SPI\Persistence\Content\Location as SPILocation;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType\IntegerField;
use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\LocationFieldMapper;

class LocationIdFieldMapper extends LocationFieldMapper
{
    public function accept(SPILocation $location)
    {
        return true;
    }

    public function mapFields(SPILocation $location)
    {
        return [
            new Field(
                'ng_location_id',
                $location->id,
                new IntegerField()
            ),
        ];
    }
}
