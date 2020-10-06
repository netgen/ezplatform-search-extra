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
class LocationIdBetween extends CriterionVisitor
{
    public function canVisit(Criterion $criterion)
    {
        return
            $criterion instanceof LocationIdCriterion
            && (
                $criterion->operator === Operator::LT
                || $criterion->operator === Operator::LTE
                || $criterion->operator === Operator::GT
                || $criterion->operator === Operator::GTE
                || $criterion->operator === Operator::BETWEEN
            );
    }

    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null)
    {
        $start = $criterion->value[0];
        $end = isset($criterion->value[1]) ? $criterion->value[1] : null;

        if ($criterion->operator === Operator::LT || $criterion->operator === Operator::LTE) {
            $end = $start;
            $start = null;
        }

        return 'ng_location_id_mi:' . $this->getRange($criterion->operator, $start, $end);
    }
}
