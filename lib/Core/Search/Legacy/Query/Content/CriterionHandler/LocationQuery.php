<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Legacy\Query\Content\CriterionHandler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\LocationQuery as LocationQueryCriterion;

/**
 * Handles the LocationQuery criterion.
 *
 * @see \Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\LocationQuery
 */
final class LocationQuery extends CriterionHandler
{
    /**
     * @var \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter
     */
    private $locationCriteriaConverter;

    public function __construct(Connection $connection, CriteriaConverter $locationCriteriaConverter)
    {
        parent::__construct($connection);

        $this->locationCriteriaConverter = $locationCriteriaConverter;
    }

    public function accept(Criterion $criterion)
    {
        return $criterion instanceof LocationQueryCriterion;
    }

    public function handle(
        CriteriaConverter $converter,
        QueryBuilder $queryBuilder,
        Criterion $criterion,
        array $languageSettings
    )
    {
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion $filter */
        $filter = $criterion->value;
        $subSelect = $this->connection->createQueryBuilder();
        $condition = $this->locationCriteriaConverter->convertCriteria($subSelect, $filter, []);

        $subSelect
            ->select('t1.contentobject_id')
            ->from('ezcontentobject_tree', 't1')
            ->innerJoin(
                't1',
                'ezcontentobject',
                't2',
                't1.contentobject_id = t2.id'
            )
            ->innerJoin(
                't2',
                'ezcontentobject_version',
                't3',
                't2.id = t3.contentobject_id'
            )
            ->where(
                $subSelect->expr()->andX(
                    $condition,
                    $subSelect->expr()->eq(
                        't2.status',
                        $queryBuilder->createNamedParameter(ContentInfo::STATUS_PUBLISHED, Types::INTEGER)
                    ),
                    $subSelect->expr()->eq(
                        't3.status',
                        $queryBuilder->createNamedParameter(VersionInfo::STATUS_PUBLISHED, Types::INTEGER)
                    )
                )
            );

        return $queryBuilder->expr()->in(
            'c.id',
            $subSelect->getSQL()
        );
    }
}
