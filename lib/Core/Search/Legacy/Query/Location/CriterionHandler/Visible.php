<?php

declare(strict_types=1);

namespace Netgen\EzPlatformSearchExtra\Core\Search\Legacy\Query\Location\CriterionHandler;

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\Visible as VisibleCriterion;

class Visible extends CriterionHandler
{
    public function accept(Criterion $criterion)
    {
        return $criterion instanceof VisibleCriterion;
    }

    public function handle(
        CriteriaConverter $converter,
        QueryBuilder $queryBuilder,
        Criterion $criterion,
        array $languageSettings
    ) {
        $isVisible = $criterion->value[0];
        $expr = $queryBuilder->expr();

        if ($isVisible) {
            return $expr->andX(
                $expr->eq(
                    't.is_hidden',
                    $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)
                ),
                $expr->eq(
                    't.is_invisible',
                    $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)
                ),
                $expr->eq(
                    'c.is_hidden',
                    $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)
                ),
            );
        }

        return $expr->orX(
            $expr->eq(
                't.is_hidden',
                $queryBuilder->createNamedParameter(1, ParameterType::INTEGER)
            ),
            $expr->eq(
                't.is_invisible',
                $queryBuilder->createNamedParameter(1, ParameterType::INTEGER)
            ),
            $expr->eq(
                'c.is_hidden',
                $queryBuilder->createNamedParameter(1, ParameterType::INTEGER)
            ),
        );
    }
}
