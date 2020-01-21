<?php

namespace Netgen\EzPlatformSearchExtra\API\Values\Content;

class SpellcheckQuery
{
    /**
     * This parameter specifies the query to spellcheck.
     *
     * @var string
     */
    public $query;

    /**
     * This parameter specifies the maximum number of suggestions
     * that the spellchecker should return for a term.
     *
     * @var int
     */
    public $count;

    /**
     * Additional Solr spellcheck params as an array to be encoded with \json_encode().
     *
     * Example:
     *
     * ```php
     *  $query->parameters = [
     *      'build': true,
     *      'reload' => true,
     *      'onlyMorePopular' => false,
     *      'maxResultsForSuggest' => 5,
     *  ];
     * ```
     *
     * @var array
     */
    public $parameters = [];
}
