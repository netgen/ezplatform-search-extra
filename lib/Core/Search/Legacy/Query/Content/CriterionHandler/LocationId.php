<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Legacy\Query\Content\CriterionHandler;

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
        $column = $this->dbHandler->quoteColumn('node_id');
        $subSelectQuery = $query->subSelect();

        switch ($criterion->operator) {
            case Criterion\Operator::EQ:
            case Criterion\Operator::IN:
                $expression = $subSelectQuery->expr->in($column, $criterion->value);
                break;

            case Criterion\Operator::GT:
            case Criterion\Operator::GTE:
            case Criterion\Operator::LT:
            case Criterion\Operator::LTE:
                $operatorFunction = $this->comparatorMap[$criterion->operator];
                $expression = $query->expr->$operatorFunction(
                    $column,
                    $subSelectQuery->bindValue(reset($criterion->value))
                );
                break;

            case Criterion\Operator::BETWEEN:
                $expression = $query->expr->between(
                    $column,
                    $subSelectQuery->bindValue($criterion->value[0]),
                    $subSelectQuery->bindValue($criterion->value[1])
                );
                break;

            default:
                throw new RuntimeException(
                    "Unknown operator '{$criterion->operator}' for LocationId criterion handler."
                );
        }

        $subSelectQuery
            ->select(
                $this->dbHandler->quoteColumn('contentobject_id')
            )->from(
                $this->dbHandler->quoteTable('ezcontentobject_tree')
            )->where($expression);

        return $query->expr->in(
            $this->dbHandler->quoteColumn('id', 'ezcontentobject'),
            $subSelectQuery
        );

    }
}
