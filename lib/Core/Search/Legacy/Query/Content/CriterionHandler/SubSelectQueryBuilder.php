<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Legacy\Query\Content\CriterionHandler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;

class SubSelectQueryBuilder extends QueryBuilder
{

    /**
     * @var \Doctrine\DBAL\Query\QueryBuilder
     */
    private $outerQueryBuilder;

    public function __construct(Connection $connection, QueryBuilder $outerQueryBuilder)
    {
        parent::__construct($connection);
        $this->outerQueryBuilder = $outerQueryBuilder;
    }

    public function createNamedParameter($value, $type = ParameterType::STRING, $placeHolder = null)
    {
        return $this->outerQueryBuilder->createNamedParameter($value, $type, $placeHolder);
    }
}
