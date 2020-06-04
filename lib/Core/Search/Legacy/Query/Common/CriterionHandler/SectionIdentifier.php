<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Legacy\Query\Common\CriterionHandler;

use Doctrine\DBAL\Connection;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use eZ\Publish\SPI\Persistence\Content\Section\Handler as SectionHandler;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\SectionIdentifier as SectionIdentifierCriterion;

/**
 * Handles the SectionIdentifier criterion.
 *
 * @see \Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\SectionIdentifier
 */
final class SectionIdentifier extends CriterionHandler
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Section\Handler
     */
    protected $sectionHandler;

    public function __construct(Connection $connection, SectionHandler $sectionHandler)
    {
        parent::__construct($connection);

        $this->sectionHandler = $sectionHandler;
    }

    public function accept(Criterion $criterion)
    {
        return $criterion instanceof SectionIdentifierCriterion;
    }

    public function handle(
        CriteriaConverter $converter,
        QueryBuilder $queryBuilder,
        Criterion $criterion,
        array $languageSettings
    ) {
        $ids = [];

        foreach ($criterion->value as $identifier) {
            $ids[] = $this->sectionHandler->loadByIdentifier($identifier)->id;
        }

        return $queryBuilder->expr()->in(
            'c.section_id',
            $queryBuilder->createNamedParameter($ids, Connection::PARAM_INT_ARRAY)
        );
    }
}
