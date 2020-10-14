<?php

declare(strict_types=1);

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\Query\Common\CriterionVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\ContentName;

/**
 * Visits the ContentName criterion.
 *
 * @see \Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\ContentName
 */
final class ContentNameRange extends CriterionVisitor
{
    public function canVisit(Criterion $criterion): bool
    {
        return
            $criterion instanceof ContentName
            && (
                $criterion->operator === Operator::LT
                || $criterion->operator === Operator::LTE
                || $criterion->operator === Operator::GT
                || $criterion->operator === Operator::GTE
                || $criterion->operator === Operator::BETWEEN
            );
    }

    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null): string
    {
        $start = $criterion->value[0];
        $end = $criterion->value[1] ?? null;

        if ($criterion->operator === Operator::LT || $criterion->operator === Operator::LTE) {
            $end = $start;
            $start = null;
        }

        return 'ng_content_name_s:' . $this->getRange($criterion->operator, $start, $end);
    }
}
