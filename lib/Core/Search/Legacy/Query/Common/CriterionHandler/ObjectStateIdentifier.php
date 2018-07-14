<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Legacy\Query\Common\CriterionHandler;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use eZ\Publish\SPI\Persistence\Content\ObjectState\Handler as ObjectStateHandler;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\ObjectStateIdentifier as ObjectStateIdentifierCriterion;

/**
 * Handles the ObjectStateIdentifier criterion.
 *
 * @see \Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\ObjectStateIdentifier
 */
final class ObjectStateIdentifier extends CriterionHandler
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Section\Handler
     */
    protected $objectStateHandler;

    /**
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $dbHandler
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler $objectStateHandler
     */
    public function __construct(
        DatabaseHandler $dbHandler,
        ObjectStateHandler $objectStateHandler
    ) {
        parent::__construct($dbHandler);

        $this->objectStateHandler = $objectStateHandler;
    }

    public function accept(Criterion $criterion)
    {
        return $criterion instanceof ObjectStateIdentifierCriterion;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function handle(
        CriteriaConverter $converter,
        SelectQuery $query,
        Criterion $criterion,
        array $languageSettings
    ) {
        $stateIdentifier = $criterion->value[0];
        $groupId = $this->objectStateHandler->loadGroupByIdentifier($criterion->target)->id;
        $stateId = $this->objectStateHandler->loadByIdentifier($stateIdentifier, $groupId)->id;
        $subQuery = $query->subSelect();

        $subQuery
            ->select($this->dbHandler->quoteColumn('contentobject_id'))
            ->from($this->dbHandler->quoteTable('ezcobj_state_link'))
            ->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('contentobject_state_id'),
                    $stateId
                )
            );

        return $query->expr->in(
            $this->dbHandler->quoteColumn('id', 'ezcontentobject'),
            $subQuery
        );
    }
}
