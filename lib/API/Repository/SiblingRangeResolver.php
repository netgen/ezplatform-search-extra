<?php

namespace Netgen\EzPlatformSearchExtra\API\Repository;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\DateMetadata;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Field as FieldCriterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Location\Depth as DepthCriterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Location\Priority as PriorityCriterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOr;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\ContentId;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\ContentName;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\DateModified;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\DatePublished;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Field;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Location\Depth;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Location\Id as LocationId;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Location\Priority;
use eZ\Publish\API\Repository\Values\ValueObject;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\ContentId as ContentIdCriterion;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\ContentName as ContentNameCriterion;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\LocationId as LocationIdCriterion;
use Netgen\EzPlatformSiteApi\API\Values\Content as SiteContent;
use Netgen\EzPlatformSiteApi\API\Values\Location as SiteLocation;
use RuntimeException;
use function count;
use function get_class;

/**
 * Resolves range Criteria, SortClauses and Query for the given Content/Location,
 * Query, SortClauses and range type.
 *
 * Supported sort clauses:
 *
 * - ContentName
 * - ContentId
 * - DateModified
 * - DatePublished
 * - Depth
 * - Field
 * - LocationId
 * - Priority
 *
 * todo: section identifier, section name, translated content name
 *
 * For Field criterion following field types are supported:
 *
 * - Checkbox
 * - Date
 * - DateAndTime
 * - EmailAddress
 * - Float
 * - Integer
 * - ISBN
 * - TextBlock
 * - TextLine
 * - Time
 */
final class SiblingRangeResolver
{
    public const RangeTypeFollowing = 'following';
    public const RangeTypePreceding = 'preceding';

    private $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @throws \Exception
     */
    public function resolveQuery(ValueObject $value, Query $query, string $rangeType): Query
    {
        $query = clone $query;

        $this->modifyQuery($value, $query, $rangeType);

        return $query;
    }

    /**
     * @throws \Exception
     */
    public function modifyQuery(ValueObject $value, Query $query, string $rangeType): void
    {
        $query->filter = new LogicalAnd([
            $query->filter,
            $this->resolveCriterion(
                $value,
                $query->sortClauses,
                $rangeType
            )
        ]);

        $query->sortClauses = $this->resolveSortClauses(
            $value,
            $query->sortClauses,
            $rangeType
        );
    }

    /**
     * @throws \Exception
     */
    public function resolveCriterion(ValueObject $value, array $sortClauses, string $rangeType): CriterionInterface
    {
        $tieBreaker = $this->getTieBreakerCriterion($value, $rangeType);

        if (empty($sortClauses)) {
            return $tieBreaker;
        }

        $count = count($sortClauses);
        $criteria = [];

        for ($i = $count; $i >= 1; $i--) {
            $groupCriteria = $this->resolveGroupCriteria($value, $sortClauses, $rangeType, $i);
            $groupCriteria[] = $tieBreaker;

            $criteria[] = new LogicalAnd($groupCriteria);
        }

        $primarySortClause = $sortClauses[0];
        $operator = $this->resolveOperator($primarySortClause, $rangeType, false);
        $criteria[] = $this->resolveCriterionForSortClause($value, $primarySortClause, $operator);

        return new LogicalOr($criteria);
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause[] $sortClauses
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\SortClause[]
     */
    public function resolveSortClauses(ValueObject $value, array $sortClauses, string $rangeType): array
    {
        $newSortClauses = [];

        foreach ($sortClauses as $sortClause) {
            $newSortClause = clone $sortClause;

            if ($rangeType === self::RangeTypePreceding) {
                $newSortClause->direction = $this->reverseDirection($sortClause);
            }

            $sortClauses[] = $newSortClause;
        }

        $newSortClauses[] = $this->getTieBreakerSortClause($value, $rangeType);

        return $newSortClauses;
    }

    /**
     * @throws \Exception
     */
    private function resolveGroupCriteria(ValueObject $value, array $sortClauses, string $rangeType, int $count): array
    {
        $criteria = [];

        for ($i = 0; $i < $count; $i++) {
            $sortClause = $sortClauses[$i];
            $operator = $this->resolveOperator($sortClause, $rangeType, true);

            $criteria[] = $this->resolveCriterionForSortClause($value, $sortClause, $operator);
        }

        return $criteria;
    }

    /**
     * @throws \Exception
     */
    private function resolveCriterionForSortClause(
        ValueObject $value,
        SortClause $sortClause,
        string $operator
    ): CriterionInterface {
        $sortClauseClass = get_class($sortClause);

        switch ($sortClauseClass) {
            case ContentId::class:
                return new ContentIdCriterion($operator, $this->getContentInfo($value)->id);
            case ContentName::class:
                return new ContentNameCriterion($operator, $this->getContentName($value));
            case DateModified::class:
                return new DateMetadata(
                    DateMetadata::MODIFIED,
                    $operator,
                    $this->getContentInfo($value)->modificationDate->getTimestamp()
                );
            case DatePublished::class:
                return new DateMetadata(
                    DateMetadata::CREATED,
                    $operator,
                    $this->getContentInfo($value)->modificationDate->getTimestamp()
                );
            case Depth::class:
                return new DepthCriterion($operator, $this->getLocation($value)->depth);
            case Field::class:
                /** @var \eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target\FieldTarget $targetData */
                $targetData = $sortClause->targetData;

                // todo logical and with content type identifier?
                return new FieldCriterion(
                    $targetData->fieldIdentifier,
                    $operator,
                    $this->getFieldValue($value, $targetData->fieldIdentifier)
                );
            case LocationId::class:
                return new LocationIdCriterion($operator, $this->getLocation($value)->id);
            case Priority::class:
                return new PriorityCriterion($operator, $this->getLocation($value)->priority);
        }

        throw new RuntimeException(
            'Sort clause "' . $sortClauseClass . '" is not supported'
        );
    }

    /**
     * @throws \Exception
     */
    private function getLocation(ValueObject $value): Location
    {
        if ($value instanceof Location) {
            return $value;
        }

        if ($value instanceof SiteLocation) {
            return $value->innerLocation;
        }

        if ($value instanceof Content) {
            return $this->repository->sudo(
                function (Repository $repository) use ($value): Location {
                    return $repository->getLocationService()->loadLocation(
                        $value->contentInfo->mainLocationId
                    );
                }
            );
        }

        if ($value instanceof SiteContent) {
            return $value->mainLocation->innerLocation;
        }

        throw new RuntimeException(
            'Value "' . get_class($value) . '" is not supported'
        );
    }

    private function getContent(ValueObject $value): Content
    {
        if ($value instanceof Location) {
            return $value->getContent();
        }

        if ($value instanceof SiteLocation) {
            return $value->content->innerContent;
        }

        if ($value instanceof Content) {
            return $value;
        }

        if ($value instanceof SiteContent) {
            return $value->innerContent;
        }

        throw new RuntimeException(
            'Value "' . get_class($value) . '" is not supported'
        );
    }

    private function getContentInfo(ValueObject $value): ContentInfo
    {
        if ($value instanceof Location || $value instanceof Content) {
            return $value->contentInfo;
        }

        if ($value instanceof SiteLocation || $value instanceof SiteContent) {
            return $value->contentInfo->innerContentInfo;
        }

        throw new RuntimeException(
            'Value "' . get_class($value) . '" is not supported'
        );
    }

    private function getContentName(ValueObject $value): string
    {
        return $this->getContent($value)->getName();
    }

    /**
     * @return mixed
     */
    private function getFieldValue(ValueObject $value, string $identifier)
    {
        $content = $this->getContent($value);

        $field = $content->getField($identifier);

        if ($field === null) {
            throw new RuntimeException(
                'Field "' . $identifier . '" not found on the given Content'
            );
        }

        switch ($field->fieldTypeIdentifier) {
            case 'ezstring':
                /** @var \eZ\Publish\Core\FieldType\TextLine\Value $value */
                $value = $field->value;

                return $value->text;
            case 'eztext':
                /** @var \eZ\Publish\Core\FieldType\TextBlock\Value $value */
                $value = $field->value;

                return $value->text;
            case 'ezdate':
                /** @var \eZ\Publish\Core\FieldType\Date\Value $value */
                $value = $field->value;

                if ($value->date === null) {
                    return null;
                }

                return $value->date->format('Y-m-d\\Z');
            case 'ezdatetime':
                /** @var \eZ\Publish\Core\FieldType\DateAndTime\Value $value */
                $value = $field->value;

                if ($value->value === null) {
                    return null;
                }

                return $value->value->getTimestamp();
            case 'eztime':
                /** @var \eZ\Publish\Core\FieldType\Time\Value $value */
                $value = $field->value;

                return $value->time;
            case 'ezemail':
                /** @var \eZ\Publish\Core\FieldType\EmailAddress\Value $value */
                $value = $field->value;

                return $value->email;
            case 'ezinteger':
                /** @var \eZ\Publish\Core\FieldType\Integer\Value $value */
                $value = $field->value;

                return $value->value;
            case 'ezfloat':
                /** @var \eZ\Publish\Core\FieldType\Float\Value $value */
                $value = $field->value;

                return $value->value;
            case 'ezboolean':
                /** @var \eZ\Publish\Core\FieldType\Checkbox\Value $value */
                $value = $field->value;

                return $value->bool;
            case 'ezisbn':
                /** @var \eZ\Publish\Core\FieldType\ISBN\Value $value */
                $value = $field->value;

                return $value->isbn;
        }

        throw new RuntimeException(
            'Field type "' . $field->fieldTypeIdentifier . '" is not supported'
        );
    }

    private function resolveOperator(SortClause $sortClause, string $rangeType, bool $inclusive): string
    {
        if ($rangeType === self::RangeTypeFollowing) {
            return $this->resolveOperatorForFollowing($sortClause, $inclusive);
        }

        if ($rangeType === self::RangeTypePreceding) {
            return $this->resolveOperatorForPreceding($sortClause, $inclusive);
        }

        throw new RuntimeException(
            'Unknown range "' . $rangeType . '"'
        );
    }

    private function resolveOperatorForFollowing(SortClause $sortClause, bool $inclusive): string
    {
        if ($sortClause->direction === Query::SORT_ASC) {
            return $inclusive ? Operator::GTE : Operator::GT;
        }

        return $inclusive ? Operator::LTE : Operator::LT;
    }

    private function resolveOperatorForPreceding(SortClause $sortClause, bool $inclusive): string
    {
        if ($sortClause->direction === Query::SORT_ASC) {
            return $inclusive ? Operator::LTE : Operator::LT;
        }

        return $inclusive ? Operator::GTE : Operator::GT;
    }

    private function getTieBreakerCriterion(ValueObject $value, string $rangeType): CriterionInterface
    {
        $operator = $this->getTieBreakerCriterionOperator($rangeType);

        if ($value instanceof Content || $value instanceof SiteContent) {
            return new ContentIdCriterion($operator, $value->id);
        }

        if ($value instanceof Location || $value instanceof SiteLocation) {
            return new LocationIdCriterion($operator, $value->id);
        }

        throw new RuntimeException(
            'Unknown range "' . $rangeType . '"'
        );
    }

    private function getTieBreakerCriterionOperator(string $rangeType): string
    {
        if ($rangeType === self::RangeTypeFollowing) {
            return Operator::GT;
        }

        if ($rangeType === self::RangeTypePreceding) {
            return Operator::LT;
        }

        throw new RuntimeException(
            'Unknown range "' . $rangeType . '"'
        );
    }

    private function reverseDirection(SortClause $sortClause): string
    {
        if ($sortClause->direction === Query::SORT_ASC) {
            return Query::SORT_DESC;
        }

        return Query::SORT_ASC;
    }

    private function getTieBreakerSortClause(ValueObject $value, string $rangeType): SortClause
    {
        $direction = $this->getSortClauseDirection($rangeType);

        if ($value instanceof Content || $value instanceof SiteContent) {
            return new ContentId($direction);
        }

        if ($value instanceof Location || $value instanceof SiteLocation) {
            return new LocationId($direction);
        }

        throw new RuntimeException(
            'Unknown range "' . $rangeType . '"'
        );
    }

    private function getSortClauseDirection(string $rangeType): string
    {
        if ($rangeType === self::RangeTypeFollowing) {
            return Query::SORT_ASC;
        }

        if ($rangeType === self::RangeTypePreceding) {
            return Query::SORT_DESC;
        }

        throw new RuntimeException(
            'Unknown range "' . $rangeType . '"'
        );
    }
}
