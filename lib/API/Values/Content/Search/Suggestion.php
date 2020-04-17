<?php

namespace Netgen\EzPlatformSearchExtra\API\Values\Content\Search;

use InvalidArgumentException;

class Suggestion
{
    /**
     * @var \Netgen\EzPlatformSearchExtra\API\Values\Content\Search\WordSuggestion[][]
     */
    private $suggestionsByOriginalWords = [];

    /**
     * @param \Netgen\EzPlatformSearchExtra\API\Values\Content\Search\WordSuggestion[] $wordSuggestions
     */
    public function __construct(array $wordSuggestions = [])
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
     * @return string[]
     */
    public function getOriginalWords()
    {
        return array_map('strval', array_keys($this->suggestionsByOriginalWords));
    }

    /**
     * @param string $originalWord
     *
     * @throws \InvalidArgumentException
     *
     * @return \Netgen\EzPlatformSearchExtra\API\Values\Content\Search\WordSuggestion[]
     */
    public function getSuggestionsByOriginalWord(string $originalWord)
    {
        if (!array_key_exists($originalWord, $this->suggestionsByOriginalWords)) {
            throw new InvalidArgumentException('No suggestions found for the given word');
        }

        return $this->suggestionsByOriginalWords[$originalWord];
    }

    /**
     * Get suggested search text based on returned spell check suggestions.
     *
     * @param string $originalSearchText
     *
     * @return string|null
     */
    public function getSuggestedSearchText(string $originalSearchText)
    {
        $originalWords = $this->getOriginalWords();
        $suggestedWords = [];

        foreach ($originalWords as $originalWord) {
            $suggestedWords[] = $this->getSuggestionsByOriginalWord($originalWord)[0]->suggestedWord;
        }

        $suggestedSearchText = \str_replace($originalWords, $suggestedWords, $originalSearchText);

        if ($originalSearchText === $suggestedSearchText) {
            return null;
        }

        return $suggestedSearchText;
    }
}
