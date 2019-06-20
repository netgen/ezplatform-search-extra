<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Legacy\Query\Common\CriterionHandler;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\UserEmail as UserEmailCriterion;
use RuntimeException;

/**
 * Handles the UserEmail criterion.
 *
 * @see \Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\UserEmail
 */
final class UserEmail extends CriterionHandler
{
    public function accept(Criterion $criterion)
    {
        return $criterion instanceof UserEmailCriterion;
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
                    $this->dbHandler->quoteColumn('email'),
                    $criterion->value
                );
                break;
            case Operator::LIKE:
                $string = $this->prepareLikeString($criterion->value);
                $expression = $query->expr->like(
                    $this->dbHandler->quoteColumn('email'),
                    $query->bindValue($string)
                );
                break;
            default:
                throw new RuntimeException(
                    "Unknown operator '{$criterion->operator}' for UserEmail criterion handler"
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

    /**
     * Returns the given $string prepared for use in SQL LIKE clause.
     *
     * LIKE clause wildcards '%' and '_' contained in the given $string will be escaped.
     *
     * @param $string
     *
     * @return string
     */
    protected function prepareLikeString($string)
    {
        $string = addcslashes($string, '%_');

        return str_replace('*', '%', $string);
    }
}
