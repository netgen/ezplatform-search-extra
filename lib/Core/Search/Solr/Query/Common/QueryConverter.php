<?php

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\Query\Common;

use eZ\Publish\API\Repository\Values\Content\Query;
use EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor;
use EzSystems\EzPlatformSolrSearchEngine\Query\FacetFieldVisitor;
use EzSystems\EzPlatformSolrSearchEngine\Query\QueryConverter as BaseQueryConverter;
use EzSystems\EzPlatformSolrSearchEngine\Query\SortClauseVisitor;
use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\FulltextSpellcheck;
use Netgen\EzPlatformSearchExtra\Core\Search\Solr\API\FacetBuilder\RawFacetBuilder;

/**
 * Converts the query tree into an array of Solr query parameters.
 */
class QueryConverter extends BaseQueryConverter
{
    /**
     * Query visitor.
     *
     * @var \EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor
     */
    protected $criterionVisitor;

    /**
     * Sort clause visitor.
     *
     * @var \EzSystems\EzPlatformSolrSearchEngine\Query\SortClauseVisitor
     */
    protected $sortClauseVisitor;

    /**
     * Facet builder visitor.
     *
     * @var \EzSystems\EzPlatformSolrSearchEngine\Query\FacetFieldVisitor
     */
    protected $facetBuilderVisitor;

    /**
     * Construct from visitors.
     *
     * @param \EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor $criterionVisitor
     * @param \EzSystems\EzPlatformSolrSearchEngine\Query\SortClauseVisitor $sortClauseVisitor
     * @param \EzSystems\EzPlatformSolrSearchEngine\Query\FacetFieldVisitor $facetBuilderVisitor
     */
    public function __construct(
        CriterionVisitor $criterionVisitor,
        SortClauseVisitor $sortClauseVisitor,
        FacetFieldVisitor $facetBuilderVisitor
    ) {
        $this->criterionVisitor = $criterionVisitor;
        $this->sortClauseVisitor = $sortClauseVisitor;
        $this->facetBuilderVisitor = $facetBuilderVisitor;
    }

    public function convert(Query $query)
    {
        $params = [
            'q' => '{!lucene}' . $this->criterionVisitor->visit($query->query),
            'fq' => '{!lucene}' . $this->criterionVisitor->visit($query->filter),
            'start' => $query->offset,
            'rows' => $query->limit,
            'fl' => '*,score,[shard]',
            'wt' => 'json',
        ];

        $sortParams = $this->getSortParams($query->sortClauses);
        if (!empty($sortParams)) {
            $params['sort'] = $sortParams;
        }

        $facetParams = $this->getFacetParams($query->facetBuilders);
        if (!empty($facetParams)) {
            $params['json.facet'] = \json_encode($facetParams);
        }

        $oldFacetParams = $this->getOldFacetParams($query->facetBuilders);
        if (!empty($oldFacetParams)) {
            $params['facet'] = 'true';
            $params['facet.sort'] = 'count';
            $params = array_merge($oldFacetParams, $params);
        }

        if ($query->query instanceof FulltextSpellcheck) {
            $spellcheckQuery = $query->query->getSpellcheckQuery();

            $params['spellcheck.q'] = $spellcheckQuery->query;
            $params['spellcheck.count'] = $spellcheckQuery->count;

            foreach ($spellcheckQuery->parameters as $key => $value) {
                $params['spellcheck.'.$key] = $value;
            }
        }

        return $params;
    }

    /**
     * Converts an array of sort clause objects to a proper Solr representation.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause[] $sortClauses
     *
     * @return string
     */
    private function getSortParams(array $sortClauses)
    {
        return implode(
            ', ',
            array_map(
                [$this->sortClauseVisitor, 'visit'],
                $sortClauses
            )
        );
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder[] $facetBuilders
     *
     * @return array
     */
    private function getFacetParams(array $facetBuilders)
    {
        $facetParams = [];
        $facetBuilders = $this->filterNewFacetBuilders($facetBuilders);

        foreach ($facetBuilders as $facetBuilder) {
            $identifier = spl_object_hash($facetBuilder);

            $facetParams[$identifier] = $this->facetBuilderVisitor->visitBuilder(
                $facetBuilder,
                null
            );
        }

        return $facetParams;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder[] $facetBuilders
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder[]
     */
    private function filterNewFacetBuilders(array $facetBuilders)
    {
        return array_filter(
            $facetBuilders,
            function ($facetBuilder) {
                return $facetBuilder instanceof RawFacetBuilder;
            }
        );
    }

    /**
     * Converts an array of facet builder objects to a Solr query parameters representation.
     *
     * This method uses spl_object_hash() to get id of each and every facet builder, as this
     * is expected by {@link \EzSystems\EzPlatformSolrSearchEngine\ResultExtractor}.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder[] $facetBuilders
     *
     * @return array
     */
    private function getOldFacetParams(array $facetBuilders)
    {
        $facetParamsGrouped = array_map(
            function ($facetBuilder) {
                return $this->facetBuilderVisitor->visitBuilder($facetBuilder, spl_object_hash($facetBuilder));
            },
            $this->filterOldFacetBuilders($facetBuilders)
        );

        return $this->formatOldFacetParams($facetParamsGrouped);
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder[] $facetBuilders
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder[]
     */
    private function filterOldFacetBuilders(array $facetBuilders)
    {
        return array_filter(
            $facetBuilders,
            function ($facetBuilder) {
                return !($facetBuilder instanceof RawFacetBuilder);
            }
        );
    }

    private function formatOldFacetParams(array $facetParamsGrouped)
    {
        $params = [];

        // In case when facet sets contain same keys, merge them in an array
        foreach ($facetParamsGrouped as $facetParams) {
            foreach ($facetParams as $key => $value) {
                if (isset($params[$key])) {
                    if (!is_array($params[$key])) {
                        $params[$key] = [$params[$key]];
                    }
                    $params[$key][] = $value;
                } else {
                    $params[$key] = $value;
                }
            }
        }

        return $params;
    }
}
