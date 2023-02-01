Spellcheck suggestions
===========

``Spellcheck suggestions`` use Solr's SpellCheck component to provide inline query suggestions based on other, similar, terms.

This could be useful to provide the "did you mean" alternative to use when the search returns no results.

.. note::

    This feature is available only with the Solr search engine.

1. Activation
~~~~~~~~~~~~~~

In order to activate this feature, Solr has to be properly configured. First we need to create a new field type and then a new field of this type for spellcheck suggestions. Then we need to copy all textual fields to it.

solr/custom-fields-types.xml additions:

.. code-block:: xml

    <fieldType name="text_suggest" class="solr.TextField" positionIncrementGap="100">
        <analyzer>
            <tokenizer class="solr.StandardTokenizerFactory"/>
            <filter class="solr.StopFilterFactory" ignoreCase="true" words="stopwords.txt" />
            <filter class="solr.LowerCaseFilterFactory"/>
        </analyzer>
    </fieldType>

     <field name="spellcheck" type="text_suggest" indexed="true" stored="true" multiValued="true" omitNorms="true" />

     <copyField source="*_t" dest="spellcheck" />

Then we need to set-up the SpellCheck component. It should already exist in solrconfig.xml but it might not be properly configured. Example configuration:

solr/solrconfig.xml additions

.. code-block:: xml

    <!-- Spell Check

       The spell check component can return a list of alternative spelling
       suggestions.

       http://wiki.apache.org/solr/SpellCheckComponent
    -->
    <searchComponent name="spellcheck" class="solr.SpellCheckComponent">
        <str name="queryAnalyzerFieldType">text_general</str>

        <!-- Multiple "Spell Checkers" can be declared and used by this
             component
          -->

        <!-- a spellchecker built from a field of the main index -->
        <lst name="spellchecker">
            <str name="name">default</str>
            <!-- decide between dictionary based vs index based spelling suggestion, in most cases it makes sense to use index based spell checker as it only generates terms which are actually present in your search corpus -->
            <str name="classname">solr.IndexBasedSpellChecker</str>
            <!-- field to use -->
            <str name="field">spellcheck</str>
            <!-- the spellcheck distance measure used, the default is the internal levenshtein -->
            <str name="distanceMeasure">org.apache.lucene.search.spell.LevenshteinDistance</str>
            <!-- buildOnCommit|buildOnOptimize -->
            <str name="buildOnCommit">true</str>
            <!-- $solr.solr.home/data/spellchecker-->
            <str name="spellcheckIndexDir">./spellchecker</str>
            <str name="accuracy">0.7</str>
            <float name="thresholdTokenFrequency">.0001</float>
        </lst>

        <!-- a spellchecker that can break or combine words.  See "/spell" handler below for usage -->
        <!--
        <lst name="spellchecker">
            <str name="name">wordbreak</str>
            <str name="classname">solr.WordBreakSolrSpellChecker</str>
            <str name="field">name</str>
            <str name="combineWords">true</str>
            <str name="breakWords">true</str>
            <int name="maxChanges">10</int>
            </lst>
            -->
    </searchComponent>

In order to get suggestions during search, we need to tell our select request handler to use the previously configured SpellCheck component.

Example request handler configuration:

.. code-block:: xml

    <!-- SearchHandler

        http://wiki.apache.org/solr/SearchHandler

        For processing Search Queries, the primary Request Handler
        provided with Solr is "SearchHandler" It delegates to a sequent
        of SearchComponents (see below) and supports distributed
        queries across multiple shards
    -->
    <requestHandler name="/select" class="solr.SearchHandler">
    <!-- default values for query parameters can be specified, these
        will be overridden by parameters in the request
    -->
    <lst name="defaults">
        <str name="echoParams">explicit</str>
        <int name="rows">10</int>
        <!-- <str name="df">text</str> -->
        <str name="spellcheck.dictionary">default</str>
        <str name="spellcheck">on</str>
        <str name="spellcheck.extendedResults">true</str>
        <str name="spellcheck.count">10</str>
        <str name="spellcheck.alternativeTermCount">5</str>
        <str name="spellcheck.maxResultsForSuggest">5</str>
        <str name="spellcheck.collate">true</str>
        <str name="spellcheck.collateExtendedResults">true</str>
        <str name="spellcheck.maxCollationTries">10</str>
        <str name="spellcheck.maxCollations">5</str>
    </lst>
    <arr name="last-components">
        <str>spellcheck</str>
    </arr>
  </requestHandler>

At last, our fulltext search criterion has to implement the ``Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\FulltextSpellcheck`` interface.

Here's the example of a criterion which extends eZ's fulltext criterion and implements the required interface:

.. code-block:: php

    <?php

    namespace AcmeBundle\API\Values\Content\Query\Criterion;

    use eZ\Publish\API\Repository\Values\Content\Query\Criterion\FullText as BaseFullTextCriterion;
    use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion\FulltextSpellcheck;
    use Netgen\EzPlatformSearchExtra\API\Values\Content\SpellcheckQuery;

    class FullTextCriterion extends BaseFullTextCriterion implements FulltextSpellcheck
    {
        /**
         * Gets query to be used for spell check.
         *
         * @return \Netgen\EzPlatformSearchExtra\API\Values\Content\SpellcheckQuery
         */
        public function getSpellcheckQuery()
        {
            $spellcheckQuery = new SpellcheckQuery();
            $spellcheckQuery->query = $this->value;
            $spellcheckQuery->count = 10;

            return $spellcheckQuery;
        }
    }

Once activated, you will get the spellcheck suggestions in SearchResult object.
