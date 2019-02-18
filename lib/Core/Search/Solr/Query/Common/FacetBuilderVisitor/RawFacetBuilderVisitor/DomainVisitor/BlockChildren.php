<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\Query\Common\FacetBuilderVisitor\RawFacetBuilderVisitor\DomainVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion\CustomField;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\SubdocumentQuery;
use Netgen\EzPlatformSearchExtra\Core\Search\Solr\API\FacetBuilder\RawFacetBuilder\Domain;
use Netgen\EzPlatformSearchExtra\Core\Search\Solr\API\FacetBuilder\RawFacetBuilder\Domain\BlockChildren as BlockChildrenDomain;
use Netgen\EzPlatformSearchExtra\Core\Search\Solr\Query\Common\FacetBuilderVisitor\RawFacetBuilderVisitor\DomainVisitor;

class BlockChildren extends DomainVisitor
{
    /**
     * @var \EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor
     */
    private $subdocumentQueryCriterionVisitor;

    /**
     * @param \EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor $subdocumentQueryCriterionVisitor
     */
    public function __construct(CriterionVisitor $subdocumentQueryCriterionVisitor)
    {
        $this->subdocumentQueryCriterionVisitor = $subdocumentQueryCriterionVisitor;
    }

    public function accept(Domain $domain)
    {
        return $domain instanceof BlockChildrenDomain;
    }

    public function visit(Domain $domain)
    {
        \assert($domain instanceof BlockChildrenDomain);

        return [
            'blockChildren' => "document_type_id:{$domain->parentDocumentIdentifier}",
            'filter' => $this->subdocumentQueryCriterionVisitor->visit(
                $this->getFilterCriteria($domain)
            ),
        ];
    }

    private function getFilterCriteria(BlockChildrenDomain $domain)
    {
        $criteria = new CustomField('document_type_id', Operator::EQ, $domain->childDocumentIdentifier);

        if ($domain->filter !== null) {
            $criteria = new LogicalAnd([$criteria, $domain->filter]);
        }

        return $criteria;
    }
}
