Custom Content subdocuments
===========================

This feature provides a way to index custom subdocuments *under* a Content document and a way to
define criteria on them using Content search.

.. note::

    This feature is available only for Solr search engine.

.. note::

    It's not possible to search for custom subdocuments directly. Instead, you can define
    subdocument criteria as a part of Content search, using ``SubdocumentQuery`` criterion.

.. note::

    Relationship between Content and it's subdocuments is not assumed. These can represent children
    under it's main Location, Content relations of a specific ContentType or something else
    altogether.

Indexing custom subdocuments
----------------------------

In order to index custom subdocuments, you will need to implement a subdocument mapper plugin.
Two extension points are provided, depending on how you want to index subdocuments:

1. Indexing custom subdocuments per Content
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To index custom subdocuments per Content, implement a service extending
``Netgen\EzPlatformSearchExtra\Core\Search\Solr\SubdocumentMapper\ContentSubdocumentMapper`` class,
defining two methods:

* ``accept(Content $content): bool``

  Here you will receive a Content that is being indexed as an instance of
  ``eZ\Publish\SPI\Persistence\Content``. Using that object you have to decide whether you want to
  index custom subdocuments for it or not.

* ``map(Content $content): Document``

  Again you will receive an instance of ``eZ\Publish\SPI\Persistence\Content``, which you can use to
  build and return an array of ``eZ\Publish\SPI\Search\Document`` instances. These represent custom
  subdocuments that will be indexed under the given Content.

Code example:

.. code-block:: php

    final class MyContentSubdocumentMapper extends ContentSubdocumentMapper
    {
        public function accept(Content $content)
        {
            return $content->versionInfo->contentInfo->contentTypeId === 42;
        }

        public function mapDocuments(Content $content)
        {
            return [
                new Document([
                    'id' => 'unique_id',
                    'fields' => [
                        new Field(
                            'document_type',
                            'content_subdocument',
                            new FieldType\IdentifierField()
                        ),
                        new Field('price', 5 new FieldType\IntegerField()),
                    ],
                ]),
            ];
        }
    }

You also have to configure the mapper in the service container configuration, tagging it with
``netgen.search.solr.subdocument_mapper.content`` tag so that the system can find it.

.. code-block:: php

    my_content_subdocument_mapper:
        class: MyContentSubdocumentMapper
        tags:
            - {name: netgen.search.solr.subdocument_mapper.content}

2. Indexing custom subdocuments per Content translation
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To index custom subdocuments per Content translation, implement a service extending
``Netgen\EzPlatformSearchExtra\Core\Search\Solr\SubdocumentMapper\ContentTranslationSubdocumentMapper``
class, defining two methods:

* ``accept(Content $content, string $languageCode): bool``

  Here you will receive a Content being indexed and a language that it's being indexed in, as an
  instance of ``eZ\Publish\SPI\Persistence\Content`` and a language code string. Using these
  parameters you have to decide whether you want to index custom subdocuments for it or not.

* ``map(Content $content, string $languageCode): Document``

  Again you receive an instance of ``eZ\Publish\SPI\Persistence\Content`` and a language code
  string. You can use these to build and return an array of ``eZ\Publish\SPI\Search\Document``
  instances, representing custom subdocuments that will be indexed under the given translation of
  a Content.

Code example:

.. code-block:: php

    final class MyContentTranslationSubdocumentMapper extends ContentSubdocumentMapper
    {
        public function accept(Content $content, $languageCode)
        {
            $contentTypeId = $content->versionInfo->contentInfo->contentTypeId;

            return $contentTypeId === 42 && $languageCode === 'cro-HR';
        }

        public function mapDocuments(Content $content, $languageCode)
        {
            return [
                new Document([
                    'id' => 'unique_subdocument_id',
                    'fields' => [
                        new Field(
                            'document_type',
                            'content_translation_subdocument',
                            new FieldType\IdentifierField()
                        ),
                        new Field('price', 5 new FieldType\IntegerField()),
                    ],
                ]),
            ];
        }
    }

You also have to configure the mapper in the service container configuration, tagging it with
``netgen.search.solr.subdocument_mapper.content_translation`` tag so that the system can find it.

.. code-block:: php

    my_content_translation_subdocument_mapper:
        class: MyContentTranslationSubdocumentMapper
        tags:
            - {name: netgen.search.solr.subdocument_mapper.content_translation}

.. note::

    It's mandatory to define ``document_type`` field of ``IdentifierField`` type, in every Document
    you are returning. You must not use ``content`` or ``location`` here, since these are already
    used by the search engine.

Using custom subdocuments in search
-----------------------------------

Indexing custom subdocuments would not be very useful without having a way to use them in search.
For this ``SubdocumentQuery`` criterion is provided. It's constructor accepts two mandatory
arguments:

1. ``string $documentTypeIdentifier``

  Document type identifier is used to match custom subdocument by it's type.

2. ``Criterion $filter``

  Filter is an instance of a criterion, with following of the standard eZ criteria being supported
  out of the box:

  * ``LogicalAnd``
  * ``LogicalNot``
  * ``LogicalOr``
  * ``CustomField``

Code example:

.. code-block:: php

    $query = new Query([
        'filter' => new LogicalAnd([
            new ContentTypeIdentifier('product'),
            new SubdocumentQuery(
                'product_variant',
                new LogicalAnd([
                    new CustomField('visible_b', Operator::EQ, true),
                    new CustomField('price_i', Operator::LT, 40),
                ])
            ),
        ])
    ]);

    $searchResult = $searchService->findContent($query);

Implementing new criteria for ``SubdocumentQuery``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you want to implement additional criteria to use with ``SubdocumentQuery`` just implement is as
usual. Then tag the visitor service with
``netgen.search.solr.query.content.criterion_visitor.subdocument_query`` tag.
