Random sort
===========

``Random`` sort.

.. note::

    This feature is available only with the Solr search engine.

solr/schema.xml additions:

.. code-block:: xml

    <!--
        This fieldtype is required to allow random sorting
    -->
    <fieldType name="random" class="solr.RandomSortField" />

    <!--
        This field is required to allow random sorting
    -->
    <dynamicField name="random*" type="random" indexed="true" stored="false"/>

For example, the following query will return random sorted content based on a provided seed:

.. code-block:: php

    use Netgen\EzPlatformSearchExtra\API\Values\Content\Query\SortClause\Random;

    $seed = (int)(time() / 360);

    new Query([
        'filter' => new LogicalAnd([
            new ContentTypeIdentifier('article'),
            new Visibility(Visibility::VISIBLE),
        ]),
        'sortClauses' =>  [
            new Random($seed),
        ]
    ]);
