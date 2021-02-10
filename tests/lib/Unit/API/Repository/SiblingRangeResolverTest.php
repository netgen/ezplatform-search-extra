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
use eZ\Publish\Core\FieldType\RelationList\Value as RelationListValue;
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
use RuntimeException;

/**
 * @group sibling-range
 */
class SiblingRangeResolverTest extends TestCase
{
    protected const Timestamp = 1612087138;

    public function providerForTestResolveCriterion(): array
    {
        $field = new Field([
            'fieldDefIdentifier' => 'field',
            'fieldTypeIdentifier' => 'ezstring',
            'languageCode' => 'cro-HR',
            'value' => new TextLineValue('Zagreb'),
        ]);

        return [
            [
                42,
                $field,
                [],
                SiblingRangeResolver::RangeTypeFollowing,
                new ContentId(Operator::GT, 42),
            ],
            [
                42,
                $field,
                [],
                SiblingRangeResolver::RangeTypePreceding,
                new ContentId(Operator::LT, 42),
            ],
            [
                42,
                $field,
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
                $field,
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
                $field,
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
                $field,
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
                $field,
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
                $field,
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
                $field,
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
                $field,
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
                $field,
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
                $field,
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
                $field,
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
                $field,
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
                $field,
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
                $field,
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
                $field,
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
                $field,
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
                $field,
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
                $field,
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
                $field,
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
                $field,
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
                $field,
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
                $field,
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
                $field,
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
                $field,
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
                $field,
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
                $field,
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
                $field,
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
                $field,
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
                $field,
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
                $field,
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
                $field,
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
                $field,
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
        $textLineField = new Field([
            'fieldDefIdentifier' => 'field',
            'fieldTypeIdentifier' => 'ezstring',
            'languageCode' => 'cro-HR',
            'value' => new TextLineValue('Zagreb'),
        ]);
        $textBlockField = new Field([
            'fieldDefIdentifier' => 'field',
            'fieldTypeIdentifier' => 'eztext',
            'languageCode' => 'cro-HR',
            'value' => new TextBlockValue('Zagreb'),
        ]);
        $dateField = new Field([
            'fieldDefIdentifier' => 'field',
            'fieldTypeIdentifier' => 'ezdate',
            'languageCode' => 'cro-HR',
            'value' => new DateValue(new DateTime('@' . self::Timestamp)),
        ]);
        $dateAndTimeField = new Field([
            'fieldDefIdentifier' => 'field',
            'fieldTypeIdentifier' => 'ezdatetime',
            'languageCode' => 'cro-HR',
            'value' => new DateAndTimeValue(new DateTime('@' . self::Timestamp)),
        ]);
        $timeField = new Field([
            'fieldDefIdentifier' => 'field',
            'fieldTypeIdentifier' => 'eztime',
            'languageCode' => 'cro-HR',
            'value' => TimeValue::fromDateTime(new DateTime('@' . self::Timestamp)),
        ]);
        $mailAddressField = new Field([
            'fieldDefIdentifier' => 'field',
            'fieldTypeIdentifier' => 'ezemail',
            'languageCode' => 'cro-HR',
            'value' => new EmailAddressValue('test@netgen.io'),
        ]);
        $integerField = new Field([
            'fieldDefIdentifier' => 'field',
            'fieldTypeIdentifier' => 'ezinteger',
            'languageCode' => 'cro-HR',
            'value' => new IntegerValue(22),
        ]);
        $floatField = new Field([
            'fieldDefIdentifier' => 'field',
            'fieldTypeIdentifier' => 'ezfloat',
            'languageCode' => 'cro-HR',
            'value' => new FloatValue(3.14),
        ]);
        $checkboxField = new Field([
            'fieldDefIdentifier' => 'field',
            'fieldTypeIdentifier' => 'ezboolean',
            'languageCode' => 'cro-HR',
            'value' => new CheckboxValue(true),
        ]);
        $isbnField = new Field([
            'fieldDefIdentifier' => 'field',
            'fieldTypeIdentifier' => 'ezisbn',
            'languageCode' => 'cro-HR',
            'value' => new ISBNValue('9780061936456'),
        ]);

        return [
            [
                42,
                $textLineField,
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
                $textLineField,
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
                $textLineField,
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
                $textLineField,
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
                $textBlockField,
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
                $textBlockField,
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
                $textBlockField,
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
                $textBlockField,
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
                $dateField,
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
                $dateField,
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
                $dateField,
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
                $dateField,
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
                $dateAndTimeField,
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
                $dateAndTimeField,
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
                $dateAndTimeField,
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
                $dateAndTimeField,
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
                $timeField,
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
                $timeField,
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
                $timeField,
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
                $timeField,
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
                $mailAddressField,
                [
                    new SortClause\Field('type', 'field', Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::GTE, 'test@netgen.io'),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new Criterion\Field('field', Operator::GT, 'test@netgen.io'),
                ]),
            ],
            [
                42,
                $mailAddressField,
                [
                    new SortClause\Field('type', 'field', Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::LTE, 'test@netgen.io'),
                        new ContentId(Operator::GT, 42),
                    ]),
                    new Criterion\Field('field', Operator::LT, 'test@netgen.io'),
                ]),
            ],
            [
                42,
                $mailAddressField,
                [
                    new SortClause\Field('type', 'field', Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::LTE, 'test@netgen.io'),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new Criterion\Field('field', Operator::LT, 'test@netgen.io'),
                ]),
            ],
            [
                42,
                $mailAddressField,
                [
                    new SortClause\Field('type', 'field', Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                new LogicalOr([
                    new LogicalAnd([
                        new Criterion\Field('field', Operator::GTE, 'test@netgen.io'),
                        new ContentId(Operator::LT, 42),
                    ]),
                    new Criterion\Field('field', Operator::GT, 'test@netgen.io'),
                ]),
            ],
            [
                42,
                $integerField,
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
                $integerField,
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
                $integerField,
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
                $integerField,
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
                $floatField,
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
                $floatField,
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
                $floatField,
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
                $floatField,
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
                $checkboxField,
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
                $checkboxField,
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
                $checkboxField,
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
                $checkboxField,
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
                $isbnField,
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
                $isbnField,
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
                $isbnField,
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
                $isbnField,
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

    /**
     * @throws \Exception
     */
    public function testResolveCriterionWithNonExistentField(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Field "field" not found on the given Content');

        $field = new Field([
            'fieldDefIdentifier' => 'hill',
            'fieldTypeIdentifier' => 'ezisbn',
            'languageCode' => 'cro-HR',
            'value' => new ISBNValue('9780061936456'),
        ]);

        $content = $this->getContent(42, $field);

        $this->getServiceUnderTest()->resolveCriterion(
            $content,
            [
                new SortClause\Field('type', 'field', Query::SORT_DESC),
            ],
            SiblingRangeResolver::RangeTypeFollowing
        );
    }

    /**
     * @throws \Exception
     */
    public function testResolveCriterionWithUnsupportedFieldType(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Field type "ezobjectrelationlist" is not supported');

        $field = new Field([
            'fieldDefIdentifier' => 'field',
            'fieldTypeIdentifier' => 'ezobjectrelationlist',
            'languageCode' => 'cro-HR',
            'value' => new RelationListValue(),
        ]);

        $content = $this->getContent(42, $field);

        $this->getServiceUnderTest()->resolveCriterion(
            $content,
            [
                new SortClause\Field('type', 'field', Query::SORT_DESC),
            ],
            SiblingRangeResolver::RangeTypeFollowing
        );
    }

    public function providerForTestResolveSortClauses(): array
    {
        $field = new Field([
            'fieldDefIdentifier' => 'field',
            'fieldTypeIdentifier' => 'ezstring',
            'languageCode' => 'cro-HR',
            'value' => new TextLineValue('Zagreb'),
        ]);

        return [
            [
                42,
                $field,
                [],
                SiblingRangeResolver::RangeTypeFollowing,
                [
                    new SortClause\ContentId(Query::SORT_ASC),
                ],
            ],
            [
                42,
                $field,
                [],
                SiblingRangeResolver::RangeTypePreceding,
                [
                    new SortClause\ContentId(Query::SORT_DESC),
                ],
            ],
            [
                42,
                $field,
                [
                    new SortClause\ContentName(Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                [
                    new SortClause\ContentId(Query::SORT_ASC),
                ],
            ],
            [
                42,
                $field,
                [
                    new SortClause\ContentName(Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                [
                    new SortClause\ContentId(Query::SORT_ASC),
                ],
            ],
            [
                42,
                $field,
                [
                    new SortClause\ContentName(Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                [
                    new SortClause\ContentId(Query::SORT_DESC),
                ],
            ],
            [
                42,
                $field,
                [
                    new SortClause\ContentName(Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                [
                    new SortClause\ContentId(Query::SORT_DESC),
                ],
            ],
            [
                42,
                $field,
                [
                    new SortClause\ContentId(Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                [
                    new SortClause\ContentId(Query::SORT_ASC),
                ],
            ],
            [
                42,
                $field,
                [
                    new SortClause\ContentId(Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                [
                    new SortClause\ContentId(Query::SORT_ASC),
                ],
            ],
            [
                42,
                $field,
                [
                    new SortClause\ContentId(Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                [
                    new SortClause\ContentId(Query::SORT_ASC),
                ],
            ],
            [
                42,
                $field,
                [
                    new SortClause\ContentId(Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                [
                    new SortClause\ContentId(Query::SORT_DESC),
                ],
            ],
            [
                42,
                $field,
                [
                    new SortClause\DateModified(Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                [
                    new SortClause\ContentId(Query::SORT_ASC),
                ],
            ],
            [
                42,
                $field,
                [
                    new SortClause\DateModified(Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                [
                    new SortClause\ContentId(Query::SORT_ASC),
                ],
            ],
            [
                42,
                $field,
                [
                    new SortClause\DateModified(Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                [
                    new SortClause\ContentId(Query::SORT_DESC),
                ],
            ],
            [
                42,
                $field,
                [
                    new SortClause\DateModified(Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                [
                    new SortClause\ContentId(Query::SORT_DESC),
                ],
            ],
            [
                42,
                $field,
                [
                    new SortClause\DatePublished(Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                [
                    new SortClause\ContentId(Query::SORT_ASC),
                ],
            ],
            [
                42,
                $field,
                [
                    new SortClause\DatePublished(Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                [
                    new SortClause\ContentId(Query::SORT_ASC),
                ],
            ],
            [
                42,
                $field,
                [
                    new SortClause\DatePublished(Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                [
                    new SortClause\ContentId(Query::SORT_DESC),
                ],
            ],
            [
                42,
                $field,
                [
                    new SortClause\DatePublished(Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                [
                    new SortClause\ContentId(Query::SORT_DESC),
                ],
            ],
            [
                42,
                $field,
                [
                    new SortClause\Location\Depth(Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                [
                    new SortClause\ContentId(Query::SORT_ASC),
                ],
            ],
            [
                42,
                $field,
                [
                    new SortClause\Location\Depth(Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                [
                    new SortClause\ContentId(Query::SORT_ASC),
                ],
            ],
            [
                42,
                $field,
                [
                    new SortClause\Location\Depth(Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                [
                    new SortClause\ContentId(Query::SORT_DESC),
                ],
            ],
            [
                42,
                $field,
                [
                    new SortClause\Location\Depth(Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                [
                    new SortClause\ContentId(Query::SORT_DESC),
                ],
            ],
            [
                42,
                $field,
                [
                    new SortClause\Field('type', 'field', Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                [
                    new SortClause\ContentId(Query::SORT_ASC),
                ],
            ],
            [
                42,
                $field,
                [
                    new SortClause\Field('type', 'field', Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                [
                    new SortClause\ContentId(Query::SORT_ASC),
                ],
            ],
            [
                42,
                $field,
                [
                    new SortClause\Field('type', 'field', Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                [
                    new SortClause\ContentId(Query::SORT_DESC),
                ],
            ],
            [
                42,
                $field,
                [
                    new SortClause\Field('type', 'field', Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                [
                    new SortClause\ContentId(Query::SORT_DESC),
                ],
            ],
            [
                42,
                $field,
                [
                    new SortClause\Location\Id(Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                [
                    new SortClause\ContentId(Query::SORT_ASC),
                ],
            ],
            [
                42,
                $field,
                [
                    new SortClause\Location\Id(Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                [
                    new SortClause\ContentId(Query::SORT_ASC),
                ],
            ],
            [
                42,
                $field,
                [
                    new SortClause\Location\Id(Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                [
                    new SortClause\ContentId(Query::SORT_DESC),
                ],
            ],
            [
                42,
                $field,
                [
                    new SortClause\Location\Id(Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                [
                    new SortClause\ContentId(Query::SORT_DESC),
                ],
            ],
            [
                42,
                $field,
                [
                    new SortClause\Location\Priority(Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                [
                    new SortClause\ContentId(Query::SORT_ASC),
                ],
            ],
            [
                42,
                $field,
                [
                    new SortClause\Location\Priority(Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypeFollowing,
                [
                    new SortClause\ContentId(Query::SORT_ASC),
                ],
            ],
            [
                42,
                $field,
                [
                    new SortClause\Location\Priority(Query::SORT_ASC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                [
                    new SortClause\ContentId(Query::SORT_DESC),
                ],
            ],
            [
                42,
                $field,
                [
                    new SortClause\Location\Priority(Query::SORT_DESC),
                ],
                SiblingRangeResolver::RangeTypePreceding,
                [
                    new SortClause\ContentId(Query::SORT_DESC),
                ],
            ],
        ];
    }

    /**
     * @dataProvider providerForTestResolveSortClauses
     *
     * @throws \Exception
     */
    public function testResolveSortClauses(
        int $id,
        Field $field,
        array $sortClauses,
        string $rangeType,
        array $expectedSortClauses
    ): void {
        $content = $this->getContent($id, $field);

        $actualSortClauses = $this->getServiceUnderTest()->resolveSortClauses(
            $content,
            $sortClauses,
            $rangeType
        );

        self::assertEquals($expectedSortClauses, $actualSortClauses);
    }

    /**
     * @throws \Exception
     */
    public function testResolverCriterionWithUnsupportedSortClause(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Sort clause "' . SortClause\MapLocationDistance::class . '" is not supported');

        $field = new Field([
            'fieldDefIdentifier' => 'field',
            'fieldTypeIdentifier' => 'ezstring',
            'languageCode' => 'cro-HR',
            'value' => new TextLineValue('Zagreb'),
        ]);

        $content = $this->getContent(42, $field);

        $this->getServiceUnderTest()->resolveCriterion(
            $content,
            [
                new SortClause\MapLocationDistance('type', 'field', 16, 42),
            ],
            SiblingRangeResolver::RangeTypeFollowing
        );
    }

    /**
     * @throws \Exception
     */
    public function testResolverCriterionWithUnsupportedRange(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown range "SomeRange"');

        $field = new Field([
            'fieldDefIdentifier' => 'field',
            'fieldTypeIdentifier' => 'ezstring',
            'languageCode' => 'cro-HR',
            'value' => new TextLineValue('Zagreb'),
        ]);

        $content = $this->getContent(42, $field);

        $this->getServiceUnderTest()->resolveCriterion(
            $content,
            [
                new SortClause\Field('type', 'field', Query::SORT_DESC),
            ],
            'SomeRange'
        );
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
