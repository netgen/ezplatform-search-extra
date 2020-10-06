<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Legacy\Query\Location\CriterionHandler;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\LocationId as LocationIdCriterion;
use RuntimeException;

/**
 * @see \Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\LocationId
 */
final class LocationId extends CriterionHandler
{
    public function accept(Criterion $criterion)
    {
        return $criterion instanceof LocationIdCriterion;
    }

    public function handle(
        CriteriaConverter $converter,
        SelectQuery $query,
        Criterion $criterion,
        array $languageSettings
    ) {
        $column = $this->dbHandler->quoteColumn('node_id', 'ezcontentobject_tree');

        switch ($criterion->operator) {
            case Criterion\Operator::EQ:
            case Criterion\Operator::IN:
                return $query->expr->in($column, $criterion->value);

            case Criterion\Operator::GT:
            case Criterion\Operator::GTE:
            case Criterion\Operator::LT:
            case Criterion\Operator::LTE:
                $operatorFunction = $this->comparatorMap[$criterion->operator];

                return $query->expr->$operatorFunction(
                    $column,
                    $query->bindValue(reset($criterion->value))
                );

            case Criterion\Operator::BETWEEN:
                return $query->expr->between(
                    $column,
                    $query->bindValue($criterion->value[0]),
                    $query->bindValue($criterion->value[1])
                );

            default:
                throw new RuntimeException(
                    "Unknown operator '{$criterion->operator}' for LocationId criterion handler."
                );
        }
    }
}
