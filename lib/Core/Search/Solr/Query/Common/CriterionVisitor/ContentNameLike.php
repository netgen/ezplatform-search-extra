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
class ContentNameLike extends CriterionVisitor
{
    public function canVisit(Criterion $criterion): bool
    {
        return $criterion instanceof ContentName && $criterion->operator === Operator::LIKE;
    }

    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null): string
    {
        $value = $criterion->value[0];

        if (strpos($value, '*') !== false) {
            return 'ng_content_name_s:' . $this->escapeExpressions($value, true);
        }

        return 'ng_content_name_s:"' . $this->escapeQuote($value) . '"';
    }
}
