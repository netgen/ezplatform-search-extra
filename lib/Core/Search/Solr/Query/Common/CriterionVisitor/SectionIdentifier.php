<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\Query\Common\CriterionVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\SPI\Persistence\Content\Section\Handler;
use EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\SectionIdentifier as SectionIdentifierCriterion;

/**
 * Visits the SectionIdentifier criterion.
 *
 * @see \Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\SectionIdentifier
 */
final class SectionIdentifier extends CriterionVisitor
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Section\Handler
     */
    protected $sectionHandler;

    /**
     * Create from content type handler and field registry.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Section\Handler $sectionHandler
     */
    public function __construct(Handler $sectionHandler)
    {
        $this->sectionHandler = $sectionHandler;
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
            $criterion instanceof SectionIdentifierCriterion
            && (
                ($criterion->operator ?: Operator::IN) === Operator::IN ||
                $criterion->operator === Operator::EQ
            );
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor $subVisitor
     *
     * @return string
     */
    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null)
    {
        $handler = $this->sectionHandler;

        $conditions = array_map(
            function ($value) use ($handler) {
                return 'content_section_id_id:"' . $handler->loadByIdentifier($value)->id . '"';
            },
            $criterion->value
        );

        return '(' . implode(' OR ', $conditions) . ')';
    }
}
