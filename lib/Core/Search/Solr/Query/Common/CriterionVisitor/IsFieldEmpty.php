<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\Query\Common\CriterionVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Search\Common\FieldNameGenerator;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;
use eZ\Publish\SPI\Search\FieldType\BooleanField;
use EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\IsFieldEmpty as IsFieldEmptyCriterion;

/**
 * Visits IsFieldEmpty criterion.
 *
 * @see \Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\IsFieldEmpty
 */
final class IsFieldEmpty extends CriterionVisitor
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
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     * @param \eZ\Publish\Core\Search\Common\FieldNameGenerator $fieldNameGenerator
     */
    public function __construct(
        ContentTypeHandler $contentTypeHandler,
        FieldNameGenerator $fieldNameGenerator
    ) {
        $this->contentTypeHandler = $contentTypeHandler;
        $this->fieldNameGenerator = $fieldNameGenerator;
    }

    public function canVisit(Criterion $criterion)
    {
        return $criterion instanceof IsFieldEmptyCriterion;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     */
    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null)
    {
        $fieldNames = $this->getFieldNames($criterion);

        if (empty($fieldNames)) {
            throw new InvalidArgumentException(
                '$criterion->target',
                "No searchable fields found for the given criterion target '{$criterion->target}'."
            );
        }

        $queries = [];

        foreach ($fieldNames as $fieldName) {
            $match = $criterion->value[0] === IsFieldEmptyCriterion::IS_EMPTY ? 'true' : 'false';
            $queries[] = "{$fieldName}:{$match}";
        }

        return '(' . implode(' OR ', $queries) . ')';
    }

    /**
     * Return all field names for the given criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return string[]
     */
    protected function getFieldNames(Criterion $criterion)
    {
        $fieldDefinitionIdentifier = $criterion->target;
        $fieldMap = $this->contentTypeHandler->getSearchableFieldMap();
        $fieldNames = [];

        foreach ($fieldMap as $contentTypeIdentifier => $fieldIdentifierMap) {
            if (!isset($fieldIdentifierMap[$fieldDefinitionIdentifier])) {
                continue;
            }

            $fieldNames[] = $this->fieldNameGenerator->getTypedName(
                $this->fieldNameGenerator->getName(
                    'ng_is_empty',
                    $fieldDefinitionIdentifier,
                    $contentTypeIdentifier
                ),
                new BooleanField()
            );
        }

        return $fieldNames;
    }
}
