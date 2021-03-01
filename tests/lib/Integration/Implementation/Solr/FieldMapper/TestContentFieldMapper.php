<?php

namespace Netgen\EzPlatformSearchExtra\Tests\Integration\Implementation\Solr\FieldMapper;

use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content as SPIContent;
use eZ\Publish\SPI\Persistence\Content\Field as ContentField;
use eZ\Publish\SPI\Persistence\Content\Type as ContentType;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType\BooleanField;
use eZ\Publish\SPI\Search\FieldType\IntegerField;
use eZ\Publish\SPI\Search\FieldType\StringField;
use eZ\Publish\SPI\Search\Handler as SearchHandler;
use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentFieldMapper;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use RuntimeException;

class TestContentFieldMapper extends ContentFieldMapper
{
    const CONTENT_TYPE_IDENTIFIER = 'extra_fields_test';

    const CHILD_CONTENT_TYPE_IDENTIFIER = 'extra_fields_test_comment';

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    private $contentTypeHandler;

    /**
     * @var \eZ\Publish\SPI\Search\Handler
     */
    private $searchHandler;

    /**
     * TestContentFieldMapper constructor.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     * @param \eZ\Publish\SPI\Search\Handler $searchHandler
     */
    public function __construct(ContentTypeHandler $contentTypeHandler, SearchHandler $searchHandler)
    {
        $this->contentTypeHandler = $contentTypeHandler;
        $this->searchHandler = $searchHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function accept(SPIContent $content)
    {
        $contentType = $this->contentTypeHandler->load(
            $content->versionInfo->contentInfo->contentTypeId
        );

        return $contentType->identifier === self::CONTENT_TYPE_IDENTIFIER;
    }

    /**
     * {@inheritdoc}
     */
    public function mapFields(SPIContent $content)
    {
        $contentType = $this->contentTypeHandler->load(
            $content->versionInfo->contentInfo->contentTypeId
        );

        $commentCount = $this->getCommentCount($content);

        $prefixedName = 'prefix '.$this->extractField($content, $contentType, 'name')->value->data;

        return [
            new Field(
                'extra_prefixed_name',
                $prefixedName,
                new StringField()
            ),
            new Field(
                'extra_comment_count',
                $commentCount,
                new IntegerField()
            ),
            new Field(
                'extra_has_comments',
                $commentCount > 0,
                new BooleanField()
            ),
        ];
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content $content
     * @param \eZ\Publish\SPI\Persistence\Content\Type $contentType
     * @param $identifier
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Field
     */
    private function extractField(Content $content, ContentType $contentType, $identifier): ContentField
    {
        $fieldDefinitionId = $this->getFieldDefinitionId($contentType, $identifier);

        foreach ($content->fields as $field) {
            if ($field->fieldDefinitionId === $fieldDefinitionId) {
                return $field;
            }
        }

        throw new RuntimeException(
            "Could not extract field '{$identifier}'"
        );
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Type $contentType
     * @param $identifier
     *
     * @return mixed
     */
    private function getFieldDefinitionId(ContentType $contentType, $identifier)
    {
        foreach ($contentType->fieldDefinitions as $fieldDefinition) {
            if ($fieldDefinition->identifier === $identifier) {
                return $fieldDefinition->id;
            }
        }

        throw new RuntimeException(
            "Could not extract field definition '{$identifier}'"
        );
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content $content
     *
     * @return int
     */
    private function getCommentCount(Content $content)
    {
        $criteria = [
            new Criterion\ParentLocationId($content->versionInfo->contentInfo->mainLocationId),
            new Criterion\ContentTypeIdentifier(self::CHILD_CONTENT_TYPE_IDENTIFIER),
        ];

        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd($criteria);
        $query->limit = 0;

        return $this->searchHandler->findLocations($query)->totalCount;
    }
}
