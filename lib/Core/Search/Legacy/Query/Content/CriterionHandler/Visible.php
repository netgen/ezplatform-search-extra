<?php

declare(strict_types=1);

namespace Netgen\EzPlatformSearchExtra\Core\Search\Legacy\Query\Content\CriterionHandler;

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
        $isHiddenInteger = (int)!$criterion->value[0];

        return $queryBuilder->expr()->eq(
            'c.is_hidden',
            $queryBuilder->createNamedParameter($isHiddenInteger, ParameterType::INTEGER)
        );
    }
}
