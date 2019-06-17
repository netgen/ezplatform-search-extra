<?php

namespace  Netgen\EzPlatformSearchExtra\API\Values\Content\Query\Criterion;

interface FulltextSpellcheck
{
    /**
     * Gets query to be used for spell check.
     *
     * @return string
     */
    public function getSpellCheckQuery();
}
