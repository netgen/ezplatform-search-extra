<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Legacy\Query\Common\CriterionHandler;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
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

    /**
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $dbHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Section\Handler $sectionHandler
     */
    public function __construct(
        DatabaseHandler $dbHandler,
        SectionHandler $sectionHandler
    ) {
        parent::__construct($dbHandler);

        $this->sectionHandler = $sectionHandler;
    }

    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return bool
     */
    public function accept(Criterion $criterion)
    {
        return $criterion instanceof SectionIdentifierCriterion;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function handle(
        CriteriaConverter $converter,
        SelectQuery $query,
        Criterion $criterion,
        array $languageSettings
    ) {
        $ids = [];

        foreach ($criterion->value as $identifier) {
            $ids[] = $this->sectionHandler->loadByIdentifier($identifier)->id;
        }

        return $query->expr->in(
            $this->dbHandler->quoteColumn('section_id', 'ezcontentobject'),
            $ids
        );
    }
}
