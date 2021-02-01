<?php


namespace Netgen\EzPlatformSearchExtra\Tests\Unit\API\Repository;

use DateTime;
use eZ\Publish\API\Repository\Values\Content\Content as APIContent;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\Location as APILocation;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\DateMetadata;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Location\Depth;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Location\Priority;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOr;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\FieldType\Checkbox\Value as CheckboxValue;
use eZ\Publish\Core\FieldType\Date\Value as DateValue;
use eZ\Publish\Core\FieldType\DateAndTime\Value as DateAndTimeValue;
use eZ\Publish\Core\FieldType\EmailAddress\Value as EmailAddressValue;
use eZ\Publish\Core\FieldType\Float\Value as FloatValue;
use eZ\Publish\Core\FieldType\Integer\Value as IntegerValue;
use eZ\Publish\Core\FieldType\ISBN\Value as ISBNValue;
use eZ\Publish\Core\FieldType\TextBlock\Value as TextBlockValue;
use eZ\Publish\Core\FieldType\TextLine\Value as TextLineValue;
use eZ\Publish\Core\FieldType\Time\Value as TimeValue;
use eZ\Publish\Core\Repository\Repository as CoreRepository;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\ContentId;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\ContentName;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\LocationId;
use Netgen\EzPlatformSearchExtra\API\Repository\SiblingRangeResolver;
use PHPUnit\Framework\TestCase;

/**
 * @group sibling-range
 */
class SiblingRangeResolverTest extends TestCase
{
    protected const Timestamp = 1612087138;

    public function providerForTestResolveCriterion(): array
    {
        return [
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [],
                SiblingRangeResolver::RangeTypeFollowing,
                new ContentId(Operator::GT, 42),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [],
                SiblingRangeResolver::RangeTypePreceding,
                new ContentId(Operator::LT, 42),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\ContentName(Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new ContentName(Operator::GTE, 'Netgen'),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new ContentName(Operator::GT, 'Netgen'),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\ContentName(Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new ContentName(Operator::LTE, 'Netgen'),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new ContentName(Operator::LT, 'Netgen'),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\ContentName(Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new ContentName(Operator::LTE, 'Netgen'),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new ContentName(Operator::LT, 'Netgen'),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\ContentName(Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new ContentName(Operator::GTE, 'Netgen'),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new ContentName(Operator::GT, 'Netgen'),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\ContentId(Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new ContentId(Operator::GTE, 42),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new ContentId(Operator::GT, 42),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\ContentId(Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new ContentId(Operator::LTE, 42),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new ContentId(Operator::LT, 42),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\ContentId(Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new ContentId(Operator::LTE, 42),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new ContentId(Operator::LT, 42),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\ContentId(Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new ContentId(Operator::GTE, 42),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new ContentId(Operator::GT, 42),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\DateModified(Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new DateMetadata(DateMetadata::MODIFIED, Operator::GTE, self::Timestamp),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new DateMetadata(DateMetadata::MODIFIED, Operator::GT, self::Timestamp),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\DateModified(Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new DateMetadata(DateMetadata::MODIFIED, Operator::LTE, self::Timestamp),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new DateMetadata(DateMetadata::MODIFIED, Operator::LT, self::Timestamp),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\DateModified(Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new DateMetadata(DateMetadata::MODIFIED, Operator::LTE, self::Timestamp),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new DateMetadata(DateMetadata::MODIFIED, Operator::LT, self::Timestamp),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\DateModified(Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new DateMetadata(DateMetadata::MODIFIED, Operator::GTE, self::Timestamp),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new DateMetadata(DateMetadata::MODIFIED, Operator::GT, self::Timestamp),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\DatePublished(Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new DateMetadata(DateMetadata::CREATED, Operator::GTE, self::Timestamp),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new DateMetadata(DateMetadata::CREATED, Operator::GT, self::Timestamp),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\DatePublished(Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new DateMetadata(DateMetadata::CREATED, Operator::LTE, self::Timestamp),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new DateMetadata(DateMetadata::CREATED, Operator::LT, self::Timestamp),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\DatePublished(Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new DateMetadata(DateMetadata::CREATED, Operator::LTE, self::Timestamp),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new DateMetadata(DateMetadata::CREATED, Operator::LT, self::Timestamp),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\DatePublished(Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new DateMetadata(DateMetadata::CREATED, Operator::GTE, self::Timestamp),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new DateMetadata(DateMetadata::CREATED, Operator::GT, self::Timestamp),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\Location\Depth(Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new Depth(Operator::GTE, 6),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new Depth(Operator::GT, 6),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\Location\Depth(Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new Depth(Operator::LTE, 6),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new Depth(Operator::LT, 6),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\Location\Depth(Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new Depth(Operator::LTE, 6),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new Depth(Operator::LT, 6),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\Location\Depth(Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new Depth(Operator::GTE, 6),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new Depth(Operator::GT, 6),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::GTE, 'Zagreb'),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new Criterion\Field('field', Operator::GT, 'Zagreb'),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::LTE, 'Zagreb'),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new Criterion\Field('field', Operator::LT, 'Zagreb'),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::LTE, 'Zagreb'),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new Criterion\Field('field', Operator::LT, 'Zagreb'),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::GTE, 'Zagreb'),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new Criterion\Field('field', Operator::GT, 'Zagreb'),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\Location\Id(Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new LocationId(Operator::GTE, 24),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new LocationId(Operator::GT, 24),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\Location\Id(Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new LocationId(Operator::LTE, 24),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new LocationId(Operator::LT, 24),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\Location\Id(Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new LocationId(Operator::LTE, 24),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new LocationId(Operator::LT, 24),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\Location\Id(Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new LocationId(Operator::GTE, 24),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new LocationId(Operator::GT, 24),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\Location\Priority(Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new Priority(Operator::GTE, 4),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new Priority(Operator::GT, 4),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\Location\Priority(Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new Priority(Operator::LTE, 4),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new Priority(Operator::LT, 4),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\Location\Priority(Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new Priority(Operator::LTE, 4),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new Priority(Operator::LT, 4),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\Location\Priority(Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new Priority(Operator::GTE, 4),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new Priority(Operator::GT, 4),
                ]),
            ],
        ];
    }

    /**
     * @dataProvider providerForTestResolveCriterion
     *
     * @throws \Exception
     */
    public function testResolveCriterion(
        int $id,
        Field $field,
        array $sortClauses,
        string $rangeType,
        CriterionInterface $expectedCriterion
    ): void {
        $content = $this->getContent($id, $field);

        $actualCriterion = $this->getServiceUnderTest()->resolveCriterion(
            $content,
            $sortClauses,
            $rangeType
        );

        self::assertEquals($expectedCriterion, $actualCriterion);
    }

    /**
     * @throws \Exception
     */
    public function providerForTestResolveCriterionField(): array
    {
        return [
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::GTE, 'Zagreb'),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new Criterion\Field('field', Operator::GT, 'Zagreb'),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::LTE, 'Zagreb'),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new Criterion\Field('field', Operator::LT, 'Zagreb'),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::LTE, 'Zagreb'),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new Criterion\Field('field', Operator::LT, 'Zagreb'),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezstring',
                    'languageCode' => 'cro-HR',
                    'value' => new TextLineValue('Zagreb'),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::GTE, 'Zagreb'),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new Criterion\Field('field', Operator::GT, 'Zagreb'),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'eztext',
                    'languageCode' => 'cro-HR',
                    'value' => new TextBlockValue('Zagreb'),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::GTE, 'Zagreb'),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new Criterion\Field('field', Operator::GT, 'Zagreb'),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'eztext',
                    'languageCode' => 'cro-HR',
                    'value' => new TextBlockValue('Zagreb'),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::LTE, 'Zagreb'),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new Criterion\Field('field', Operator::LT, 'Zagreb'),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'eztext',
                    'languageCode' => 'cro-HR',
                    'value' => new TextBlockValue('Zagreb'),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::LTE, 'Zagreb'),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new Criterion\Field('field', Operator::LT, 'Zagreb'),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'eztext',
                    'languageCode' => 'cro-HR',
                    'value' => new TextBlockValue('Zagreb'),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::GTE, 'Zagreb'),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new Criterion\Field('field', Operator::GT, 'Zagreb'),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezdate',
                    'languageCode' => 'cro-HR',
                    'value' => new DateValue(new DateTime('@' . self::Timestamp)),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::GTE, '2021-01-31Z'),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new Criterion\Field('field', Operator::GT, '2021-01-31Z'),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezdate',
                    'languageCode' => 'cro-HR',
                    'value' => new DateValue(new DateTime('@' . self::Timestamp)),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::LTE, '2021-01-31Z'),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new Criterion\Field('field', Operator::LT, '2021-01-31Z'),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezdate',
                    'languageCode' => 'cro-HR',
                    'value' => new DateValue(new DateTime('@' . self::Timestamp)),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::LTE, '2021-01-31Z'),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new Criterion\Field('field', Operator::LT, '2021-01-31Z'),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezdate',
                    'languageCode' => 'cro-HR',
                    'value' => new DateValue(new DateTime('@' . self::Timestamp)),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::GTE, '2021-01-31Z'),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new Criterion\Field('field', Operator::GT, '2021-01-31Z'),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezdatetime',
                    'languageCode' => 'cro-HR',
                    'value' => new DateAndTimeValue(new DateTime('@' . self::Timestamp)),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::GTE, self::Timestamp),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new Criterion\Field('field', Operator::GT, self::Timestamp),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezdatetime',
                    'languageCode' => 'cro-HR',
                    'value' => new DateAndTimeValue(new DateTime('@' . self::Timestamp)),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::LTE, self::Timestamp),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new Criterion\Field('field', Operator::LT, self::Timestamp),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezdatetime',
                    'languageCode' => 'cro-HR',
                    'value' => new DateAndTimeValue(new DateTime('@' . self::Timestamp)),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::LTE, self::Timestamp),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new Criterion\Field('field', Operator::LT, self::Timestamp),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezdatetime',
                    'languageCode' => 'cro-HR',
                    'value' => new DateAndTimeValue(new DateTime('@' . self::Timestamp)),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::GTE, self::Timestamp),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new Criterion\Field('field', Operator::GT, self::Timestamp),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'eztime',
                    'languageCode' => 'cro-HR',
                    'value' => TimeValue::fromDateTime(new DateTime('@' . self::Timestamp)),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::GTE, 35938),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new Criterion\Field('field', Operator::GT, 35938),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'eztime',
                    'languageCode' => 'cro-HR',
                    'value' => TimeValue::fromDateTime(new DateTime('@' . self::Timestamp)),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::LTE, 35938),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new Criterion\Field('field', Operator::LT, 35938),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'eztime',
                    'languageCode' => 'cro-HR',
                    'value' => TimeValue::fromDateTime(new DateTime('@' . self::Timestamp)),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::LTE, 35938),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new Criterion\Field('field', Operator::LT, 35938),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'eztime',
                    'languageCode' => 'cro-HR',
                    'value' => TimeValue::fromDateTime(new DateTime('@' . self::Timestamp)),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::GTE, 35938),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new Criterion\Field('field', Operator::GT, 35938),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezemail',
                    'languageCode' => 'cro-HR',
                    'value' => new EmailAddressValue('spam@invalid.asdf'),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::GTE, 'spam@invalid.asdf'),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new Criterion\Field('field', Operator::GT, 'spam@invalid.asdf'),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezemail',
                    'languageCode' => 'cro-HR',
                    'value' => new EmailAddressValue('spam@invalid.asdf'),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::LTE, 'spam@invalid.asdf'),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new Criterion\Field('field', Operator::LT, 'spam@invalid.asdf'),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezemail',
                    'languageCode' => 'cro-HR',
                    'value' => new EmailAddressValue('spam@invalid.asdf'),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::LTE, 'spam@invalid.asdf'),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new Criterion\Field('field', Operator::LT, 'spam@invalid.asdf'),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezemail',
                    'languageCode' => 'cro-HR',
                    'value' => new EmailAddressValue('spam@invalid.asdf'),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::GTE, 'spam@invalid.asdf'),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new Criterion\Field('field', Operator::GT, 'spam@invalid.asdf'),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezinteger',
                    'languageCode' => 'cro-HR',
                    'value' => new IntegerValue(22),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::GTE, 22),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new Criterion\Field('field', Operator::GT, 22),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezinteger',
                    'languageCode' => 'cro-HR',
                    'value' => new IntegerValue(22),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::LTE, 22),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new Criterion\Field('field', Operator::LT, 22),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezinteger',
                    'languageCode' => 'cro-HR',
                    'value' => new IntegerValue(22),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::LTE, 22),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new Criterion\Field('field', Operator::LT, 22),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezinteger',
                    'languageCode' => 'cro-HR',
                    'value' => new IntegerValue(22),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::GTE, 22),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new Criterion\Field('field', Operator::GT, 22),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezfloat',
                    'languageCode' => 'cro-HR',
                    'value' => new FloatValue(3.14),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::GTE, 3.14),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new Criterion\Field('field', Operator::GT, 3.14),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezfloat',
                    'languageCode' => 'cro-HR',
                    'value' => new FloatValue(3.14),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::LTE, 3.14),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new Criterion\Field('field', Operator::LT, 3.14),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezfloat',
                    'languageCode' => 'cro-HR',
                    'value' => new FloatValue(3.14),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::LTE, 3.14),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new Criterion\Field('field', Operator::LT, 3.14),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezfloat',
                    'languageCode' => 'cro-HR',
                    'value' => new FloatValue(3.14),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::GTE, 3.14),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new Criterion\Field('field', Operator::GT, 3.14),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezboolean',
                    'languageCode' => 'cro-HR',
                    'value' => new CheckboxValue(true),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::GTE, true),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new Criterion\Field('field', Operator::GT, true),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezboolean',
                    'languageCode' => 'cro-HR',
                    'value' => new CheckboxValue(true),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::LTE, true),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new Criterion\Field('field', Operator::LT, true),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezboolean',
                    'languageCode' => 'cro-HR',
                    'value' => new CheckboxValue(true),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::LTE, true),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new Criterion\Field('field', Operator::LT, true),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezboolean',
                    'languageCode' => 'cro-HR',
                    'value' => new CheckboxValue(true),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::GTE, true),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new Criterion\Field('field', Operator::GT, true),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezisbn',
                    'languageCode' => 'cro-HR',
                    'value' => new ISBNValue('9780061936456'),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::GTE, '9780061936456'),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new Criterion\Field('field', Operator::GT, '9780061936456'),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezisbn',
                    'languageCode' => 'cro-HR',
                    'value' => new ISBNValue('9780061936456'),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::LTE, '9780061936456'),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new Criterion\Field('field', Operator::LT, '9780061936456'),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezisbn',
                    'languageCode' => 'cro-HR',
                    'value' => new ISBNValue('9780061936456'),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::LTE, '9780061936456'),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new Criterion\Field('field', Operator::LT, '9780061936456'),
                ]),
            ],
            [
                42,
                new Field([
                    'fieldDefIdentifier' => 'field',
                    'fieldTypeIdentifier' => 'ezisbn',
                    'languageCode' => 'cro-HR',
                    'value' => new ISBNValue('9780061936456'),
                ]),
                [
                    new SortClause\Field('type', 'field', Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::GTE, '9780061936456'),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new Criterion\Field('field', Operator::GT, '9780061936456'),
                ]),
            ],
        ];
    }

    /**
     * @dataProvider providerForTestResolveCriterionField
     *
     * @throws \Exception
     */
    public function testResolveCriterionField(
        int $id,
        Field $field,
        array $sortClauses,
        string $rangeType,
        CriterionInterface $expectedCriterion
    ): void {
        $content = $this->getContent($id, $field);

        $actualCriterion = $this->getServiceUnderTest()->resolveCriterion(
            $content,
            $sortClauses,
            $rangeType
        );

        self::assertEquals($expectedCriterion, $actualCriterion);
    }

    public function testResolveSortClauses(): void
    {

    }

    public function testModifyQuery(): void
    {

    }

    public function testResolveQuery(): void
    {

    }

    /**
     * @throws \Exception
     */
    protected function getContent(int $id, Field $field): APIContent
    {
        return new Content([
            'versionInfo' => new VersionInfo([
                'contentInfo' => new ContentInfo([
                    'id' => $id,
                    'modificationDate' => new DateTime('@' . self::Timestamp),
                    'mainLanguageCode' => 'cro-HR',
                ]),
                'initialLanguageCode' => 'cro-HR',
                'names' => [
                    'cro-HR' => 'Netgen',
                ],

            ]),
            'internalFields' => [
                'field' => $field,
            ],
        ]);
    }

    protected function getLocation(): APILocation
    {
        return new Location([
            'id' => 24,
            'depth' => 6,
            'priority' => 4,
        ]);
    }

    protected function getServiceUnderTest(): SiblingRangeResolver
    {
        return new SiblingRangeResolver($this->getRepositoryMock());
    }

    protected function getRepositoryMock()
    {
        $repositoryMock = $this
            ->getMockBuilder(CoreRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repoContent = $this->getLocation();
        $repositoryMock->method('sudo')->willReturn($repoContent);

        return $repositoryMock;
    }
}
