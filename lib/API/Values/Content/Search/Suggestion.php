<?php

namespace Netgen\EzPlatformSearchExtra\API\Values\Content\Search;

use InvalidArgumentException;

class Suggestion
{
    /**
     * @var \Netgen\EzPlatformSearchExtra\API\Values\Content\Search\WordSuggestion[]
     */
    private $suggestionsByOriginalWords = [];

    /**
     * Suggestion constructor.
     *
     * @param \Netgen\EzPlatformSearchExtra\API\Values\Content\Search\WordSuggestion[] $wordSuggestions
     */
    public function __construct(array $wordSuggestions)
    {
        foreach ($wordSuggestions as $suggestion) {
            if (!array_key_exists($suggestion->originalWord, $this->suggestionsByOriginalWords)) {
                $this->suggestionsByOriginalWords[$suggestion->originalWord] = [];
            }

            $this->suggestionsByOriginalWords[$suggestion->originalWord][] = $suggestion;
        }
    }

    /**
     * @return bool
     */
    public function hasSuggestions()
    {
        return !empty($this->suggestionsByOriginalWords);
    }

    /**
     * @return \Netgen\EzPlatformSearchExtra\API\Values\Content\Search\WordSuggestion[]
     */
    public function getSuggestions()
    {
        return array_values($this->suggestionsByOriginalWords);
    }

    /**
     * @return array
     */
    public function getOriginalWords()
    {
        return array_keys($this->suggestionsByOriginalWords);
    }

    /**
     * @param string $originalWord
     *
     * @return \Netgen\EzPlatformSearchExtra\API\Values\Content\Search\WordSuggestion[]
     *
     * @throws \InvalidArgumentException
     */
    public function getSuggestionsByOriginalWord(string $originalWord)
    {
        if (!array_key_exists($originalWord, $this->suggestionsByOriginalWords)) {
            throw new InvalidArgumentException();
        }

        return $this->suggestionsByOriginalWords[$originalWord];
    }
}
