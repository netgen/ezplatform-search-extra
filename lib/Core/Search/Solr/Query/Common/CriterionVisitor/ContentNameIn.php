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
final class ContentNameIn extends CriterionVisitor
{
    public function canVisit(Criterion $criterion): bool
    {
        return
            $criterion instanceof ContentName
            && (
                $criterion->operator === Operator::IN
                || $criterion->operator === Operator::EQ
            );
    }

    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null): string
    {
        $values = [];

        foreach ($criterion->value as $value) {
            $values[] = 'ng_content_name_s:"' . $this->escapeQuote($value, true) . '"';
        }

        return '(' . implode(' OR ', $values) . ')';
    }
}
