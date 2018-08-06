LocationQuery criterion
=======================

``LocationQuery`` criterion can be used with Content search. It allows grouping of Location criteria
so that they apply together on a Location.

.. note::

    This feature is available with both Solr and Legacy search engines.

For example, the following query will return Content of type ``article`` if it has hidden Location in
subtree ``/1/2/10/`` and visible Location in some other subtree:

.. code-block:: php

    new Query([
        'filter' => new LogicalAnd([
            new ContentTypeIdentifier('article'),
            new Subtree('/1/2/10/'),
            new Visibility(Visibility::VISIBLE),
        ]),
    ]);

Using ``LocationQuery`` criterion you can write a query that will return Content of type ``article``
only when it has a visible Location in subtree ``/1/2/10/``:

.. code-block:: php

    new Query([
        'filter' => new LogicalAnd([
            new ContentTypeIdentifier('article'),
            new LocationQuery(
                new LogicalAnd([
                    new Subtree('/1/2/10/'),
                    new Visibility(Visibility::VISIBLE),
                ])
            )
        ]),
    ]);
