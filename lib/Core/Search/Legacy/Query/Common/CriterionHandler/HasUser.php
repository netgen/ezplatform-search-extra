<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Legacy\Query\Common\CriterionHandler;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\HasUser as HasUserCriterion;

/**
 * Handles the HasUser criterion.
 *
 * @see \Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\HasUser
 */
final class HasUser extends CriterionHandler
{
    public function accept(Criterion $criterion)
    {
        return $criterion instanceof HasUserCriterion;
    }

    public function handle(
        CriteriaConverter $converter,
        QueryBuilder $queryBuilder,
        Criterion $criterion,
        array $languageSettings
    ) {
        $subQuery = $this->connection->createQueryBuilder();
        $hasUser = reset($criterion->value);

        $subQuery
            ->select('t1.contentobject_id')
            ->from('ezuser', 't1')
            ->where(
                $subQuery->expr()->eq('t1.contentobject_id', 'c.contentobject_id')
            );

        if ($hasUser === true) {
            return $queryBuilder->expr()->in(
                'c.id',
                $subQuery->getSQL()
            );
        }

        return $queryBuilder->expr()->notIn(
            'c.id',
            $subQuery->getSQL()
        );
    }
}
