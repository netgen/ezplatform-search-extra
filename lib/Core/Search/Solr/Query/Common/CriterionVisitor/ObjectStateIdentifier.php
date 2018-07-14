<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\Query\Common\CriterionVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\SPI\Persistence\Content\ObjectState\Handler;
use EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\ObjectStateIdentifier as ObjectStateIdentifierCriterion;

/**
 * Visits the ObjectStateIdentifier criterion.
 *
 * @see \Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\ObjectStateIdentifier
 */
final class ObjectStateIdentifier extends CriterionVisitor
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler
     */
    protected $objectStateHandler;

    /**
     * Create from object state Handler.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler $objectStateHandler
     */
    public function __construct(Handler $objectStateHandler)
    {
        $this->objectStateHandler = $objectStateHandler;
    }

    /**
     * Check if visitor is applicable to current criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return bool
     */
    public function canVisit(Criterion $criterion)
    {
        return
            $criterion instanceof ObjectStateIdentifierCriterion
            && $criterion->operator === Operator::EQ;
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If ObjectStateGroup or ObjectState is not found
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor $subVisitor
     *
     * @return string
     */
    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null)
    {
        $stateIdentifier = $criterion->value[0];
        $groupId = $this->objectStateHandler->loadGroupByIdentifier($criterion->target)->id;
        $stateId = $this->objectStateHandler->loadByIdentifier($stateIdentifier, $groupId)->id;

        return 'content_object_state_ids_mid:"' . $stateId . '"';
    }
}
