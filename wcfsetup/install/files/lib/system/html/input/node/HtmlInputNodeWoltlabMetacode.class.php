<?php

namespace wcf\system\html\input\node;

use wcf\data\bbcode\BBCodeCache;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\bbcode\HtmlBBCodeParser;
use wcf\system\html\metacode\converter\IMetacodeConverter;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\util\DOMUtil;
use wcf\util\StringUtil;

/**
 * Processes `<woltlab-metacode>` and converts them if appropriate.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       3.0
 */
class HtmlInputNodeWoltlabMetacode extends AbstractHtmlInputNode
{
    /**
     * static mapping of attribute-less metacodes that map to
     * an exact HTML tag without the need of further processing
     *
     * @var string[]
     */
    public $simpleMapping = [
        'b' => 'strong',
        'i' => 'em',
        'tt' => 'kbd',
        'u' => 'u',
    ];

    /**
     * @inheritDoc
     */
    protected $tagName = 'woltlab-metacode';

    /**
     * @inheritDoc
     */
    public function isAllowed(AbstractHtmlNodeProcessor $htmlNodeProcessor)
    {
        $bbcodes = [];
        /** @var \DOMElement $element */
        foreach ($htmlNodeProcessor->getDocument()->getElementsByTagName($this->tagName) as $element) {
            $bbcodes[] = \mb_strtolower($element->getAttribute('data-name'));
        }

        $bbcodes = \array_unique($bbcodes);

        $disallowedBBCodes = [];
        foreach ($bbcodes as $bbcode) {
            if (BBCodeHandler::getInstance()->isAvailableBBCode($bbcode, true)) {
                continue;
            }

            $disallowedBBCodes[] = $bbcode;
        }

        return $disallowedBBCodes;
    }

    /**
     * @inheritDoc
     */
    public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor)
    {
        /** @var IMetacodeConverter[] $converters */
        $converters = [];

        /** @var \DOMElement $element */
        foreach ($elements as $element) {
            if (DOMUtil::isRemoved($element) || $element->parentNode === null) {
                // ignore elements that existed, but have been removed
                // from the DOM due to action taken by a converter
                continue;
            }

            $actualName = $element->getAttribute('data-name');
            $name = \mb_strtolower($actualName);
            if ($name !== $actualName) {
                // Normalize the attribute name even if no further processing is
                // performed.
                $element->setAttribute('data-name', $name);
            }

            if ($name === 'abstract') {
                continue;
            }

            // handle simple mapping types
            if (isset($this->simpleMapping[$name])) {
                $newElement = $element->ownerDocument->createElement($this->simpleMapping[$name]);
                DOMUtil::replaceElement($element, $newElement);

                continue;
            }

            $attributes = $htmlNodeProcessor->parseAttributes($element->getAttribute('data-attributes'));
            if (!$this->validateAttributes($name, $attributes)) {
                // The attributes violate the definition of the bbcode, discard the
                // element but keep its content.
                DOMUtil::removeNode($element, true);

                continue;
            }

            if ($attributes === []) {
                // Drop the empty 'data-attributes' attribute. This saves some storage
                // for common parameterless BBCodes and makes the edit history more useful.
                // This change is safe, because a missing attribute will implicitly be
                // considered to contain the empty string by ->getAttribute().
                $element->removeAttribute('data-attributes');
            }

            // check for converters
            $converter = $converters[$name] ?? null;
            if ($converter === null) {
                $className = 'wcf\\system\\html\\metacode\\converter\\' . \ucfirst($name) . 'MetacodeConverter';
                if (\class_exists($className)) {
                    $converter = new $className();

                    $converters[$name] = $converter;
                }
            }

            if ($converter === null) {
                // check if the bbcode's content should be used as first attribute and it
                // matches the elements content
                $bbcode = BBCodeCache::getInstance()->getBBCodeByTag($name);
                if ($bbcode !== null) {
                    $bbcodeAttributes = $bbcode->getAttributes();
                    $attr = $bbcodeAttributes[0] ?? null;

                    if (
                        $attr !== null
                        && $attr->useText
                        && !empty($attributes[0])
                        && StringUtil::trim($attributes[0]) == StringUtil::trim($element->textContent)
                    ) {
                        // discard content as it is already present in the first attribute
                        while ($element->childNodes->length) {
                            $element->removeChild($element->childNodes->item(0));
                        }
                    }
                }

                // no available converter, metacode will be handled during output generation
                continue;
            } elseif (!BBCodeHandler::getInstance()->isAvailableBBCode($name)) {
                // skip conversion of disallowed bbcodes
                continue;
            }

            /** @var IMetacodeConverter $converter */
            if ($converter->validateAttributes($attributes)) {
                $fragment = DOMUtil::childNodesToFragment($element);
                if (!$fragment->hasChildNodes()) {
                    $fragment->appendChild($fragment->ownerDocument->createTextNode(''));
                }

                $newElement = $converter->convert($fragment, $attributes);
                // @phpstan-ignore instanceof.alwaysTrue
                if (!($newElement instanceof \DOMElement)) {
                    throw new \UnexpectedValueException("Expected a valid DOMElement as return value.");
                }

                DOMUtil::replaceElement($element, $newElement);

                // We're explicitly throwing away the doc fragment, as any remaining
                // nodes will otherwise stick around for a while. They continue to exist
                // until `createElement` or `createDocumentFragment` are called which
                // cause an internal GC process that throws away the children, making
                // the end of their lifetime unpredictable. Thanks PHP.
                unset($fragment);
            } else {
                // attributes are invalid, remove element from DOM
                DOMUtil::removeNode($element, true);
            }
        }
    }

    /**
     * Validates the attributes of a metacode against the definition of the bbcode.
     *
     * `<woltlab-metacode>` is part of the accepted input format, therefore the attributes
     * can be provided by the user without ever being processed by the bbcode parser. The
     * attributes are eventually embedded into the HTML, either by a converter or during the
     * output generation, thus they must be held to the same standard as a plain bbcode.
     *
     * @param array<string|int, string|int|float|bool|null> $attributes
     */
    protected function validateAttributes(string $name, array $attributes): bool
    {
        $bbcode = BBCodeCache::getInstance()->getBBCodeByTag($name);
        if ($bbcode === null) {
            // Unknown bbcodes are emitted as plain text during the output generation.
            return true;
        }

        return HtmlBBCodeParser::getInstance()->isValidTag([
            'name' => $name,
            'closing' => false,
            'source' => '',
            'attributes' => \array_map(\strval(...), $attributes),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function replaceTag(array $data)
    {
        return $data['parsedTag'];
    }
}
