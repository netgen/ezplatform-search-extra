<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\FieldMapper\ContentTranslation;

use eZ\Publish\Core\Persistence\FieldTypeRegistry;
use eZ\Publish\Core\Search\Common\FieldNameGenerator;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Field as PersistenceField;
use eZ\Publish\SPI\Persistence\Content\Type as ContentType;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType\BooleanField;
use EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentTranslationFieldMapper;

/**
 * Indexes information on whether Content field value is empty.
 */
class IsFieldEmptyFieldMapper extends ContentTranslationFieldMapper
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    private $contentTypeHandler;

    /**
     * @var \eZ\Publish\Core\Search\Common\FieldNameGenerator
     */
    private $fieldNameGenerator;

    /**
     * @var \Netgen\EzPlatformSearchExtra\Core\Persistence\FieldTypeRegistry
     */
    private $fieldTypeRegistry;

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     * @param \eZ\Publish\Core\Search\Common\FieldNameGenerator $fieldNameGenerator
     * @param \eZ\Publish\Core\Persistence\FieldTypeRegistry $fieldTypeRegistry
     */
    public function __construct(
        ContentTypeHandler $contentTypeHandler,
        FieldNameGenerator $fieldNameGenerator,
        FieldTypeRegistry $fieldTypeRegistry
    ) {
        $this->contentTypeHandler = $contentTypeHandler;
        $this->fieldNameGenerator = $fieldNameGenerator;
        $this->fieldTypeRegistry = $fieldTypeRegistry;
    }

    public function accept(Content $content, $languageCode)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function mapFields(Content $content, $languageCode)
    {
        $fieldsGrouped = [[]];
        $contentType = $this->contentTypeHandler->load(
            $content->versionInfo->contentInfo->contentTypeId
        );

        foreach ($content->fields as $field) {
            if ($field->languageCode !== $languageCode) {
                continue;
            }

            $fieldsGrouped[] = $this->mapField($contentType, $field);
        }

        return array_merge(...$fieldsGrouped);
    }

    private function mapField(ContentType $contentType, PersistenceField $field)
    {
        $fields = [];

        foreach ($contentType->fieldDefinitions as $fieldDefinition) {
            if ($fieldDefinition->id !== $field->fieldDefinitionId) {
                continue;
            }

            /** @var \Netgen\EzPlatformSearchExtra\Core\Persistence\FieldType $fieldType */
            $fieldType = $this->fieldTypeRegistry->getFieldType($fieldDefinition->fieldType);

            $fields[] = new Field(
                $name = $this->fieldNameGenerator->getName(
                    'is_empty',
                    $fieldDefinition->identifier,
                    $contentType->identifier
                ),
                $fieldType->isEmptyValue($field->value),
                new BooleanField()
            );
        }

        return $fields;
    }
}
