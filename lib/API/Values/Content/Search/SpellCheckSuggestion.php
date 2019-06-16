<?php

namespace Netgen\EzPlatformSearchExtra\API\Values\Content\Search;

use eZ\Publish\API\Repository\Values\ValueObject;

class SpellCheckSuggestion extends ValueObject
{
    /**
     * @var string
     */
    public $word;

    /**
     * @var int
     */
    public $frequency;
}
