<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Legacy\Query\Common\CriterionHandler;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
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
        SelectQuery $query,
        Criterion $criterion,
        array $languageSettings
    ) {
        $subQuery = $query->subSelect();
        $hasUser = reset($criterion->value);

        $subQuery
            ->select($this->dbHandler->quoteColumn('contentobject_id'))
            ->from($this->dbHandler->quoteTable('ezuser'))
            ->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('contentobject_id', 'ezuser'),
                    $this->dbHandler->quoteColumn('id', 'ezcontentobject')
                )
            );

        $expression = $query->expr->in(
            $this->dbHandler->quoteColumn('id', 'ezcontentobject'),
            $subQuery
        );

        if ($hasUser === true) {
            return $expression;
        }

        return $query->expr->not($expression);
    }
}
