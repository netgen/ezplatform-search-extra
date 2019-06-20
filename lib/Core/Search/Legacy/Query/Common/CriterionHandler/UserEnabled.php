<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Legacy\Query\Common\CriterionHandler;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
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
        SelectQuery $query,
        Criterion $criterion,
        array $languageSettings
    ) {
        $subQuery = $query->subSelect();
        $enabled = reset($criterion->value);

        $subQuery
            ->select($this->dbHandler->quoteColumn('contentobject_id'))
            ->from($this->dbHandler->quoteTable('ezuser'))
            ->innerJoin(
                $this->dbHandler->quoteTable('ezuser_setting'),
                'ezuser.contentobject_id',
                'ezuser_setting.user_id'
            )
            ->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('is_enabled', 'ezuser_setting'),
                    $enabled ? 1 : 0
                )
            );

        return $query->expr->in(
            $this->dbHandler->quoteColumn('id', 'ezcontentobject'),
            $subQuery
        );
    }
}
