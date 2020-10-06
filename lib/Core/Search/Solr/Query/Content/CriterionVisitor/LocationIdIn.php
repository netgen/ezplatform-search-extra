<?php


namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\Query\Content\CriterionVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\LocationId as LocationIdCriterion;

/**
 * Visits the LocationId criterion.
 *
 * @see \Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\LocationId
 */
class LocationIdIn extends CriterionVisitor
{
    public function canVisit(Criterion $criterion)
    {
        return
            $criterion instanceof LocationIdCriterion
            && (
                $criterion->operator === Operator::IN
                || $criterion->operator === Operator::EQ
            );
    }

    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null)
    {
        $values = array();

        foreach ($criterion->value as $value) {
            $values[] = 'ng_location_id_mi:"' . $value . '"';
        }

        return '(' . implode(' OR ', $values) . ')';
    }
}
