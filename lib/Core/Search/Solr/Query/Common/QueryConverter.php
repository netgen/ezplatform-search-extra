<?php

declare(strict_types=1);

namespace Netgen\EzPlatformSearchExtra\Core\Search\Solr\Query\Common;

use eZ\Publish\API\Repository\Values\Content\Query;
use EzSystems\EzPlatformSolrSearchEngine\Query\AggregationVisitor;
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
    protected $criterionVisitor;
    protected $sortClauseVisitor;
    protected $facetBuilderVisitor;
    private $aggregationVisitor;

    public function __construct(
        CriterionVisitor $criterionVisitor,
        SortClauseVisitor $sortClauseVisitor,
        FacetFieldVisitor $facetBuilderVisitor,
        AggregationVisitor $aggregationVisitor
    ) {
        $this->criterionVisitor = $criterionVisitor;
        $this->sortClauseVisitor = $sortClauseVisitor;
        $this->facetBuilderVisitor = $facetBuilderVisitor;
        $this->aggregationVisitor = $aggregationVisitor;
    }

    public function convert(Query $query, array $languageSettings = []): array
    {
        $params = [
            'q' => '{!lucene}' . $this->criterionVisitor->visit($query->query),
            'fq' => '{!lucene}' . $this->criterionVisitor->visit($query->filter),
            'sort' => $this->getSortParams($query->sortClauses),
            'start' => $query->offset,
            'rows' => $query->limit,
            'fl' => '*,score,[shard]',
            'wt' => 'json',
        ];

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

        if (!empty($query->aggregations)) {
            $aggregations = [];

            foreach ($query->aggregations as $aggregation) {
                if ($this->aggregationVisitor->canVisit($aggregation, $languageSettings)) {
                    $aggregations[$aggregation->getName()] = $this->aggregationVisitor->visit(
                        $this->aggregationVisitor,
                        $aggregation,
                        $languageSettings
                    );
                }
            }

            if (!empty($aggregations)) {
                $params['json.facet'] = json_encode($aggregations);
            }
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
    private function getSortParams(array $sortClauses): string
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
    private function getFacetParams(array $facetBuilders): array
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
    private function filterNewFacetBuilders(array $facetBuilders): array
    {
        return array_filter(
            $facetBuilders,
            static function ($facetBuilder) {
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
    private function getOldFacetParams(array $facetBuilders): array
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
    private function filterOldFacetBuilders(array $facetBuilders): array
    {
        return array_filter(
            $facetBuilders,
            static function ($facetBuilder) {
                return !($facetBuilder instanceof RawFacetBuilder);
            }
        );
    }

    private function formatOldFacetParams(array $facetParamsGrouped): array
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
