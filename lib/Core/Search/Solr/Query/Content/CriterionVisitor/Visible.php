<?php

declare(strict_types=1);

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\Query\Content\CriterionVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\Visible as VisibleCriterion;

class Visible extends CriterionVisitor
{
    public function canVisit(Criterion $criterion)
    {
        return $criterion instanceof VisibleCriterion;
    }

    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null)
    {
        $isVisible = $criterion->value[0];

        return 'ng_content_visible_b:' . ($isVisible ? 'true' : 'false');
    }
}
