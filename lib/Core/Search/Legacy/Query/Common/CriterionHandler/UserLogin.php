<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Legacy\Query\Common\CriterionHandler;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\UserLogin as UserLoginCriterion;
use RuntimeException;

/**
 * Handles the UserLogin criterion.
 *
 * @see \Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\UserLogin
 */
final class UserLogin extends CriterionHandler
{
    public function accept(Criterion $criterion)
    {
        return $criterion instanceof UserLoginCriterion;
    }

    public function handle(
        CriteriaConverter $converter,
        SelectQuery $query,
        Criterion $criterion,
        array $languageSettings
    ) {
        $subQuery = $query->subSelect();

        switch ($criterion->operator) {
            case Operator::EQ:
            case Operator::IN:
                $expression = $query->expr->in(
                    $this->dbHandler->quoteColumn('login'),
                    $criterion->value
                );
                break;
            case Operator::LIKE:
                $expression = $query->expr->like(
                    $this->dbHandler->quoteColumn('login'),
                    $criterion->value
                );
                break;
            default:
                throw new RuntimeException(
                    "Unknown operator '{$criterion->operator}' for UserLogin criterion handler"
                );
        }

        $subQuery
            ->select($this->dbHandler->quoteColumn('contentobject_id'))
            ->from($this->dbHandler->quoteTable('ezuser'))
            ->where($expression);

        return $query->expr->in(
            $this->dbHandler->quoteColumn('id', 'ezcontentobject'),
            $subQuery
        );
    }
}
