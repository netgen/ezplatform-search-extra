Extra fields from Solr
======================

This feature allows you to extract additionally indexed Solr fields from each SearchHit in SearchResult. For example, you can index some fields from children content on the parent content and then get those fields during search (eg. children count).

.. note::

    This feature is available only with the Solr search engine.

1. Usage
~~~~~~~~

In order for this functionality to work, you have to use overridden `Netgen\EzPlatformSearchExtra\API\Values\Content\Search\Query` or `Netgen\EzPlatformSearchExtra\API\Values\Content\Search\LocationQuery` queries and use it's property `extraFields` to provide a list of additional fields that you want to extract from the Solr document. Those fields, if exist, and their values will appear in the `extraFields` property of each `SearchHit` object contained in the `SearchResult.`

2. Example
~~~~~~~~~~

Example of a content field mapper:

.. code-block:: php

    public function mapFields(SPIContent $content)
    {
        return [
            new Field(
                'extra_field_example',
                5,
                new IntegerField()
            ),
        ];
    }

Search example:

.. code-block:: php

    /** @var \Netgen\EzPlatformSearchExtra\API\Values\Content\Search\Query $query **/
    $query = new Query();

    $query->extraFields = [
        'extra_field_example_i',
    ];

    /** @var \Netgen\EzPlatformSiteApi\API\FindService $findService **/
    $searchResult = $findService->findContent($query);

    /** @var \Netgen\EzPlatformSearchExtra\API\Values\Content\Search\SearchHit $searchHit **/
    foreach ($searchResult->searchHits as $searchHit) {
        var_dump($searchHit->extraFields);
    }

This will output the following data:

.. code-block:: shell

    array(1) { ["extra_field_example_i"]=> int(5) }
