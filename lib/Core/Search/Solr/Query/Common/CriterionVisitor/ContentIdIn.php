<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\Query\Common\CriterionVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\ContentId as ContentIdCriterion;

/**
 * Visits the ContentId criterion.
 *
 * @see \Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\ContentId
 */
final class ContentIdIn extends CriterionVisitor
{
    public function canVisit(Criterion $criterion)
    {
        return
            $criterion instanceof ContentIdCriterion
            && (
                $criterion->operator === Operator::IN
                || $criterion->operator === Operator::EQ
            );
    }

    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null)
    {
        $values = array();

        foreach ($criterion->value as $value) {
            $values[] = 'ng_content_id_i:"' . $value . '"';
        }

        return '(' . implode(' OR ', $values) . ')';
    }
}
