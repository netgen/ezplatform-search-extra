<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Legacy\Query\Common\CriterionHandler;

use Doctrine\DBAL\Types\Types;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\UserEnabled as UserEnabledCriterion;

/**
 * Handles the UserEnabled criterion.
 *
 * @see \Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\UserEnabled
 */
final class UserEnabled extends CriterionHandler
{
    public function accept(Criterion $criterion)
    {
        return $criterion instanceof UserEnabledCriterion;
    }

    public function handle(
        CriteriaConverter $converter,
        QueryBuilder $queryBuilder,
        Criterion $criterion,
        array $languageSettings
    ) {
        $subQuery = $this->connection->createQueryBuilder();
        $enabled = reset($criterion->value);

        $subQuery
            ->select('t1.contentobject_id')
            ->from('ezuser', 't1')
            ->innerJoin(
                't1',
                'ezuser_setting',
                't2',
                't1.contentobject_id = t2.user_id'
            )
            ->where(
                $subQuery->expr()->eq(
                    't2.is_enabled',
                    $queryBuilder->createNamedParameter($enabled ? 1 : 0, Types::INTEGER)
                )
            );

        return $queryBuilder->expr()->in(
            'c.id',
            $subQuery->getSQL()
        );
    }
}
