# Extra bits for eZ Platform search

[![Build Status](https://img.shields.io/travis/netgen/ezplatform-search-extra.svg?style=flat-square)](https://travis-ci.org/netgen/ezplatform-search-extra)
[![Read the Docs](https://img.shields.io/readthedocs/pip.svg?style=flat-square)](https://netgen-ezplatform-search-extra.readthedocs.io/en/latest/index.html)
[![Downloads](https://img.shields.io/packagist/dt/netgen/ezplatform-search-extra.svg?style=flat-square)](https://packagist.org/packages/netgen/ezplatform-search-extra)
[![Latest stable](https://img.shields.io/github/release/netgen/ezplatform-search-extra.svg?style=flat-square)](https://packagist.org/packages/netgen/ezplatform-search-extra)
[![License](https://img.shields.io/github/license/netgen/ezplatform-search-extra.svg?style=flat-square)](https://packagist.org/packages/netgen/ezplatform-search-extra)

## Features

Only a short list of features is provided here, see
[documentation](https://netgen-ezplatform-search-extra.readthedocs.io)
for more details.

- [`IsFieldEmpty`](https://github.com/netgen/ezplatform-search-extra/blob/master/lib/API/Values/Content/Query/Criterion/IsFieldEmpty.php) criterion (`solr`)

  Requires initial reindexing after installation.

- [`ObjectStateIdentifier`](https://github.com/netgen/ezplatform-search-extra/blob/master/lib/API/Values/Content/Query/Criterion/ObjectStateIdentifier.php) criterion (`solr`, `legacy`)
- [`SectionIdentifier`](https://github.com/netgen/ezplatform-search-extra/blob/master/lib/API/Values/Content/Query/Criterion/SectionIdentifier.php) criterion (`solr`, `legacy`)
- Support for custom Content subdocuments (Solr search engine) (`solr`)

  Provides a way to index custom subdocuments to Content document and
  [`SubdocumentQuery`](https://github.com/netgen/ezplatform-search-extra/blob/master/lib/API/Values/Content/Query/Criterion/SubdocumentQuery.php)
  criterion, available in Content search to define grouped conditions for a custom subdocument.

- [`SubdocumentField`](https://github.com/netgen/ezplatform-search-extra/blob/master/lib/API/Values/Content/Query/SortClause/SubdocumentField.php) sort clause (`solr`)

  Provides a way to sort Content by a subdocument field, choosing scoring calculation mode and optionally limiting with `SubdocumentQuery` criterion.

  **Note:** This will require Solr `6.6` or higher in order to work correctly with all scoring modes.

- [`LocationQuery`](https://github.com/netgen/ezplatform-search-extra/blob/master/lib/API/Values/Content/Query/Criterion/LocationQuery.php) criterion (`solr`, `legacy`)

  Allows grouping of Location criteria so that they apply together on a Location.

- [`CustomFieldFacetBuilder`](https://github.com/netgen/ezplatform-search-extra/blob/master/lib/API/Values/Content/Query/FacetBuilder/CustomFieldFacetBuilder.php) facet builder (`solr`)

  Allows building facets on custom Solr fields.

- [`RawFacetBuilder`](https://github.com/netgen/ezplatform-search-extra/blob/master/lib/Core/Search/Solr/API/FacetBuilder/RawFacetBuilder.php) facet builder (`solr`)

  Exposes Solr's [JSON facet API](https://lucene.apache.org/solr/guide/7_4/json-facet-api.html) in full.

- [`Score`](https://github.com/netgen/ezplatform-search-extra/blob/master/lib/API/Values/Content/Query/SortClause/Score.php) sort clause (`solr`)

  Provides a way to sort Content by score value.

- [`Random`](https://github.com/netgen/ezplatform-search-extra/blob/master/lib/API/Values/Content/Query/SortClause/Random.php) sort clause (`solr`)

  Provides a way to sort Content randomly.
  
- Indexable implementations for [`RichText`](https://github.com/netgen/ezplatform-search-extra/blob/master/lib/Core/FieldType/RichText/Indexable.php) and [`XmlText`](https://github.com/netgen/ezplatform-search-extra/blob/master/lib/Core/FieldType/XmlText/Indexable.php) (`solr`)

  These implementations shorten text indexed as keyword to 256 characters, which prevents failures
  when the field's content is too big for Solr's string field. They can be controlled with
  semantic configuration (showing defaults):

  ```yaml
  netgen_ez_platform_search_extra:
      indexable_field_type:
          ezxmltext:
              enabled: true
              short_text_limit: 256
          ezrichtext:
              enabled: true
              short_text_limit: 256
  ```

- [`Loading implementation`](https://github.com/netgen/ezplatform-search-extra/blob/master/lib/Core/FieldType/RichText/Indexable.php) of result extractor (`solr`)

  Loading result extractor gets it's value objects by loading them from the persistence. This
  prevents:

    - `UnauthorizedException` failures because of the missing `content/versionread` permission
    when Content is updated and incremented current version number is not yet indexed in Solr
    - `NotFoundException` failures when Content/Location is deleted and the corresponding document
    is not yet removed from Solr index

  Usage of loading result extractor can be controlled with semantic configuration (showing defaults):

  ```yaml
  netgen_ez_platform_search_extra:
      use_loading_search_result_extractor: true
  ```

## Installation

To install eZ Platform Search Extra first add it as a dependency to your project:

```sh
composer require netgen/ezplatform-search-extra:^1.4
```

Once the added dependency is installed, activate the bundle in `app/AppKernel.php` file by adding it to the `$bundles` array in `registerBundles()` method, together with other required bundles:

```php
public function registerBundles()
{
    ...

    $bundles[] = new Netgen\Bundle\EzPlatformSearchExtraBundle\NetgenEzPlatformSearchExtraBundle;

    return $bundles;
}
```
