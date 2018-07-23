<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\Query\Common\FacetBuilderVisitor;

use EzSystems\EzPlatformSolrSearchEngine\Query\FacetBuilderVisitor;
use EzSystems\EzPlatformSolrSearchEngine\Query\FacetFieldVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Search\Facet\CustomFieldFacet;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\FacetBuilder\CustomFieldFacetBuilder;

/**
 * Visits the CustomField facet builder.
 *
 * @see \Netgen\EzPlatformSearchExtra\API\Values\Content\Query\FacetBuilder\CustomFieldFacetBuilder
 */
class CustomFieldFacetBuilderVisitor extends FacetBuilderVisitor implements FacetFieldVisitor
{
    public function canVisit(FacetBuilder $facetBuilder)
    {
        return $facetBuilder instanceof CustomFieldFacetBuilder;
    }

    /**
     * Returns facet sort parameter.
     *
     * @param \Netgen\EzPlatformSearchExtra\API\Values\Content\Query\FacetBuilder\CustomFieldFacetBuilder $facetBuilder
     *
     * @return string
     */
    private function getSort(CustomFieldFacetBuilder $facetBuilder)
    {
        switch ($facetBuilder->sort) {
            case CustomFieldFacetBuilder::COUNT_DESC:
                return 'count';
            case CustomFieldFacetBuilder::TERM_ASC:
                return 'index';
        }

        return 'index';
    }

    public function mapField($field, array $data, FacetBuilder $facetBuilder)
    {
        return new CustomFieldFacet([
            'name' => $facetBuilder->name,
            'entries' => $this->mapData($data),
        ]);
    }

    public function visitBuilder(FacetBuilder $facetBuilder, $fieldId)
    {
        /** @var \Netgen\EzPlatformSearchExtra\API\Values\Content\Query\FacetBuilder\CustomFieldFacetBuilder $facetBuilder */
        $fieldName = $facetBuilder->fieldName;

        return [
            'facet.field' => "{!ex=dt key={$fieldId}}{$fieldName}",
            "f.{$fieldName}.facet.limit" => $facetBuilder->limit,
            "f.{$fieldName}.facet.mincount" => $facetBuilder->minCount,
            "f.{$fieldName}.facet.sort" => $this->getSort($facetBuilder),
        ];
    }
}
