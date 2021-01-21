<?php

namespace Netgen\EzPlatformSearchExtra\Tests\Integration\Implementation\Solr\FieldMapper;

use eZ\Publish\SPI\Persistence\Content\Location as SPILocation;
use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\LocationFieldMapper;

class TestLocationFieldMapper extends LocationFieldMapper implements TestFieldMapperInterface
{
    use TestFieldMapperTrait;

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Location $location
     *
     * @return bool
     */
    public function accept(SPILocation $location)
    {
        $content = $this->contentHandler->load($location->contentId);

        return $this->accepts($content);
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Location $location
     *
     * @return \eZ\Publish\SPI\Search\Field[]|void
     */
    public function mapFields(SPILocation $location)
    {
        $content = $this->contentHandler->load($location->contentId);

        return $this->getFields($content);
    }
}
