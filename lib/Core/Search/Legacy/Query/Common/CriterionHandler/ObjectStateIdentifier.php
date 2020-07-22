<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Legacy\Query\Common\CriterionHandler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Doctrine\DBAL\Query\QueryBuilder;
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

    public function __construct(Connection $connection, ObjectStateHandler $objectStateHandler)
    {
        parent::__construct($connection);

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
        QueryBuilder $queryBuilder,
        Criterion $criterion,
        array $languageSettings
    ) {
        $stateIdentifier = $criterion->value[0];
        $groupId = $this->objectStateHandler->loadGroupByIdentifier($criterion->target)->id;
        $stateId = $this->objectStateHandler->loadByIdentifier($stateIdentifier, $groupId)->id;
        $subQuery = $this->connection->createQueryBuilder();

        $subQuery
            ->select('t1.contentobject_id')
            ->from('ezcobj_state_link', 't1')
            ->where(
                $subQuery->expr()->eq(
                    't1.contentobject_state_id',
                    $queryBuilder->createNamedParameter($stateId, Types::INTEGER)
                )
            );

        return $queryBuilder->expr()->in(
            'c.id',
            $subQuery->getSQL()
        );
    }
}
