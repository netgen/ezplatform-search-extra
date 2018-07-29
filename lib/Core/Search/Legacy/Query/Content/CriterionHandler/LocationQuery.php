<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Legacy\Query\Content\CriterionHandler;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\LocationQuery as LocationQueryCriterion;
use PDO;

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

    /**
     * LocationQuery constructor.
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $dbHandler
     * @param \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter $locationCriteriaConverter
     */
    public function __construct(DatabaseHandler $dbHandler, CriteriaConverter $locationCriteriaConverter)
    {
        $this->locationCriteriaConverter = $locationCriteriaConverter;

        parent::__construct($dbHandler);
    }

    public function accept(Criterion $criterion)
    {
        return $criterion instanceof LocationQueryCriterion;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function handle(
        CriteriaConverter $converter,
        SelectQuery $query,
        Criterion $criterion,
        array $languageSettings
    )
    {
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion $filter */
        $filter = $criterion->value;
        $subSelect = $query->subSelect();
        $condition = $this->locationCriteriaConverter->convertCriteria($subSelect, $filter, []);

        $subSelect
            ->select('ezcontentobject_tree.contentobject_id')
            ->from($this->dbHandler->quoteTable('ezcontentobject_tree'))
            ->innerJoin(
                $this->dbHandler->quoteTable('ezcontentobject'),
                $this->dbHandler->quoteColumn('contentobject_id', 'ezcontentobject_tree'),
                $this->dbHandler->quoteColumn('id', 'ezcontentobject')
            )
            ->innerJoin(
                $this->dbHandler->quoteTable('ezcontentobject_version'),
                $this->dbHandler->quoteColumn('id', 'ezcontentobject'),
                $this->dbHandler->quoteColumn('contentobject_id', 'ezcontentobject_version')
            )
            ->where(
                $condition,
                //ContentInfo::STATUS_PUBLISHED
                $subSelect->expr->eq(
                    'ezcontentobject.status',
                    $subSelect->bindValue(1, null, PDO::PARAM_INT)
                ),
                //VersionInfo::STATUS_PUBLISHED
                $subSelect->expr->eq(
                    'ezcontentobject_version.status',
                    $subSelect->bindValue(1, null, PDO::PARAM_INT)
                )
            );

        return $query->expr->in(
            $this->dbHandler->quoteColumn('id', 'ezcontentobject'),
            $subSelect
        );
    }
}
