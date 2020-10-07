<?php

declare(strict_types=1);

namespace Netgen\EzPlatformSearchExtra\Core\Search\Legacy\Query\Location\CriterionHandler;

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\LocationId as LocationIdCriterion;
use RuntimeException;

/**
 * @see \Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\LocationId
 */
final class LocationId extends CriterionHandler
{
    public function accept(Criterion $criterion): bool
    {
        return $criterion instanceof LocationIdCriterion;
    }

    public function handle(
        CriteriaConverter $converter,
        QueryBuilder $queryBuilder,
        Criterion $criterion,
        array $languageSettings
    ) {
        $column = 't.node_id';

        switch ($criterion->operator) {
            case Operator::EQ:
            case Operator::IN:
                return $queryBuilder->expr()->in($column, $criterion->value);

            case Operator::GT:
            case Operator::GTE:
            case Operator::LT:
            case Operator::LTE:
                $operatorFunction = $this->comparatorMap[$criterion->operator];

                return $queryBuilder->expr()->$operatorFunction(
                    $column,
                    $queryBuilder->createNamedParameter(reset($criterion->value), ParameterType::INTEGER)
                );

            case Operator::BETWEEN:
                return $this->dbPlatform->getBetweenExpression(
                    $column,
                    $queryBuilder->createNamedParameter($criterion->value[0], ParameterType::INTEGER),
                    $queryBuilder->createNamedParameter($criterion->value[1], ParameterType::INTEGER)
                );

            default:
                throw new RuntimeException(
                    "Unknown operator '{$criterion->operator}' for LocationId criterion handler."
                );
        }
    }
}
