<?php

namespace Netgen\EzPlatformSearchExtra\Core\FieldType\RichText;

use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\FieldType\Indexable as IndexableInterface;
use eZ\Publish\SPI\Search;
use DOMDocument;
use DOMNode;

/**
 * Indexable definition for RichText field type.
 */
final class Indexable implements IndexableInterface
{
    /**
     * @var int
     */
    private $shortTextMaxLength;

    public function __construct($shortTextMaxLength = 256)
    {
        $this->shortTextMaxLength = $shortTextMaxLength;
    }

    public function getIndexData(Field $field, FieldDefinition $fieldDefinition)
    {
        $document = new DOMDocument();
        $document->loadXML($field->value->data);
        $text = $this->extractText($document->documentElement);
        $shortText = $this->shortenText($text);

        return [
            new Search\Field(
                'fulltext',
                $text,
                new Search\FieldType\FullTextField()
            ),
            new Search\Field(
                'value',
                $shortText,
                new Search\FieldType\StringField()
            ),
        ];
    }

    /**
     * Extracts text content of the given $node.
     *
     * @param \DOMNode $node
     *
     * @return string
     */
    private function extractText(DOMNode $node)
    {
        $text = '';

        if ($node->childNodes !== null && $node->childNodes->length > 0) {
            foreach ($node->childNodes as $child) {
                $text .= $this->extractText($child);
            }
        } else {
            $text .= $node->nodeValue . ' ';
        }

        return $text;
    }

    /**
     * Shorten text from the given $text.
     *
     * @param string $text
     *
     * @return string
     */
    private function shortenText($text)
    {
        return mb_substr(trim(strtok($text, "\r\n")), 0, $this->shortTextMaxLength);
    }

    public function getIndexDefinition()
    {
        return [
            'value' => new Search\FieldType\StringField(),
        ];
    }

    public function getDefaultMatchField()
    {
        return 'value';
    }

    public function getDefaultSortField()
    {
        return $this->getDefaultMatchField();
    }
}
