<?php

namespace Netgen\EzPlatformSearchExtra\API\Values\Content\Search;

use eZ\Publish\API\Repository\Values\ValueObject;

class WordSuggestion extends ValueObject
{
    /**
     * @var string
     */
    public $originalWord;

    /**
     * @var string
     */
    public $suggestedWord;

    /**
     * @var int
     */
    public $frequency;
}
