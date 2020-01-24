<?php

namespace Netgen\EzPlatformSearchExtra\Tests\API;

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
