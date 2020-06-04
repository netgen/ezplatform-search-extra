<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Legacy\Query\Common\CriterionHandler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use Doctrine\DBAL\Query\QueryBuilder;
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
        QueryBuilder $queryBuilder,
        Criterion $criterion,
        array $languageSettings
    ) {
        $subQuery = $this->connection->createQueryBuilder();

        switch ($criterion->operator) {
            case Operator::EQ:
            case Operator::IN:
                $expression = $subQuery->expr()->in(
                    't1.email',
                    $queryBuilder->createNamedParameter($criterion->value, Connection::PARAM_STR_ARRAY)
                );
                break;
            case Operator::LIKE:
                $string = $this->prepareLikeString($criterion->value);
                $expression = $subQuery->expr()->like(
                    't1.email',
                    $queryBuilder->createNamedParameter($string, Types::STRING)
                );
                break;
            default:
                throw new RuntimeException(
                    "Unknown operator '{$criterion->operator}' for UserEmail criterion handler"
                );
        }

        $subQuery
            ->select('t1.contentobject_id')
            ->from('ezuser', 't1')
            ->where($expression);

        return $queryBuilder->expr()->in(
            'c.id',
            $subQuery->getSQL()
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
