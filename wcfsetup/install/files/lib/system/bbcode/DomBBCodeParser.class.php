<?php

namespace wcf\system\bbcode;

use wcf\data\bbcode\BBCodeCache;
use wcf\system\SingletonFactory;
use wcf\util\JSON;
use wcf\util\StringUtil;

/**
 * Parses bbcodes in the DOM and transforms them into the custom HTML element `<woltlab-metacode-marker>`.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class DomBBCodeParser extends SingletonFactory
{
    /**
     * @var array{uuid: string, metacodeMarker: \DOMElement}[]
     */
    private array $openTagIdentifiers = [];
    /**
     * @var \DOMElement[]
     */
    private array $closingTags = [];

    private \DOMDocument $document;
    /**
     * @var array{uuid: string, metacodeMarker: \DOMElement, attributeNo: int}[]
     */
    private array $useTextNodes = [];

    /**
     * tag names used to isolate bbcodes contained in source code elements
     * @var string[]
     */
    public static array $codeTagNames = ['kbd', 'pre'];

    /**
     * Parses bbcodes in the given DOM document.
     */
    public function parse(\DOMDocument $document): void
    {
        $this->openTagIdentifiers = $this->closingTags = $this->useTextNodes = [];
        $this->document = $document;
        foreach ($document->getElementsByTagName('body')->item(0)->childNodes as $node) {
            $this->convertBBCodeToMetacodeMarker($node);
        }

        // find correct closing tags
        foreach ($this->closingTags as $node) {
            $name = $node->getAttribute('data-name');
            $node->removeAttribute('data-name');

            if (!isset($this->openTagIdentifiers[$name]) || empty($this->openTagIdentifiers[$name])) {
                $this->insertBBCode($node);
                continue;
            }
            ['uuid' => $uuid] = \array_shift($this->openTagIdentifiers[$name]);
            $node->setAttribute('data-uuid', $uuid);
        }

        // Insert raw BB-code text for each opening tag without a corresponding closing tag.
        foreach ($this->openTagIdentifiers as $entries) {
            foreach ($entries as ['metacodeMarker' => $node]) {
                $this->insertBBCode($node);
            }
        }

        // Get the text between the opening and closing tags
        // and remove it from the DOM.
        $nodes = [];
        foreach ($this->useTextNodes as ['uuid' => $uuid, 'metacodeMarker' => $node, 'attributeNo' => $attributeNo]) {
            $nextNode = $node->nextSibling;
            while ($nextNode !== null) {
                if ($nextNode->nodeType === \XML_TEXT_NODE) {
                    $nextNode = $nextNode->nextSibling;
                    continue;
                }
                \assert($nextNode instanceof \DOMElement);

                if (
                    $nextNode->nodeName === 'woltlab-metacode-marker'
                    && $nextNode->getAttribute('data-uuid') === $uuid
                ) {
                    break;
                }

                if ($nextNode->nodeName === 'woltlab-metacode-marker') {
                    $nextNode = $nextNode->nextSibling;
                    continue;
                }

                $nextNode = $nextNode->nextSibling;
            }

            if ($nextNode === null) {
                continue;
            }

            $text = '';
            $currentNode = $node->nextSibling;
            while ($currentNode !== $nextNode) {
                $nodes[] = $currentNode;

                $text .= $currentNode->textContent;
                $currentNode = $currentNode->nextSibling;
            }

            if ($node->hasAttribute('data-attributes')) {
                $attributes = JSON::decode(\base64_decode($node->getAttribute('data-attributes')));
            } else {
                $attributes = [];
            }
            $attributes[$attributeNo] = $text;
            $node->setAttribute('data-attributes', \base64_encode(JSON::encode($attributes)));
        }
        foreach ($nodes as $node) {
            $node->parentNode->removeChild($node);
        }
    }

    private function convertBBCodeToMetacodeMarker(\DOMNode $node): void
    {
        if (\in_array($node->nodeName, DomBBCodeParser::$codeTagNames)) {
            // don't parse bbcode inside code tags
            return;
        }

        if ($node->nodeType === \XML_TEXT_NODE) {
            \assert($node instanceof \DOMText);
            $this->parseNode($node);
        } else {
            foreach ($node->childNodes as $child) {
                $this->convertBBCodeToMetacodeMarker($child);
            }
        }
    }

    private function parseNode(\DOMText $node): void
    {
        /** @see BBCodeParser::buildTagArray() */
        // build tag pattern
        $validTags = \implode('|', \array_keys(BBCodeCache::getInstance()->getBBCodes()));
        $pattern = '~\[(?:/(?:' . $validTags . ')|(?:' . $validTags . ')
			(?:=
				(?:\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|[^,\]]*)
				(?:,(?:\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|[^,\]]*))*
			)?)\]~ix';

        // get bbcode tags
        \preg_match_all($pattern, $node->textContent, $matches);
        foreach ($matches[0] as $bbcodeTag) {
            $metaCodeMarker = $this->createMetacodeMarker($bbcodeTag);
            if ($metaCodeMarker === null) {
                continue;
            }

            $bbcodeNode = $node->splitText(\mb_strpos($node->textContent, $bbcodeTag));
            $node = $bbcodeNode->splitText(\mb_strlen($bbcodeTag));

            $bbcodeNode->parentNode->replaceChild($metaCodeMarker, $bbcodeNode);
        }
    }

    private function createMetacodeMarker(string $bbcodeTag): ?\DOMElement
    {
        $attributes = [];
        if (\mb_substr($bbcodeTag, 1, 1) == '/') {
            // closing tag
            $name = \mb_strtolower(\mb_substr($bbcodeTag, 2, \mb_strlen($bbcodeTag) - 3));
            $isClosingTag = true;
        } else {
            // opening tag
            // split tag and attributes
            \preg_match("!^\\[([a-z0-9]+)=?(.*)]$!si", $bbcodeTag, $match);
            $name = \mb_strtolower($match[1]);

            // build attributes
            if (!empty($match[2])) {
                $attributes = BBCodeParser::getInstance()->buildTagAttributes($match[2]);
            }
            $isClosingTag = false;
        }

        $bbcode = BBCodeCache::getInstance()->getBBCodeByTag($name);
        if ($bbcode === null) {
            return null;
        }

        $metacodeMarker = $this->document->createElement('woltlab-metacode-marker');
        $metacodeMarker->setAttribute('data-source', \base64_encode($bbcodeTag));
        $metacodeMarker->setAttribute('data-name', $name);

        if ($isClosingTag) {
            $this->closingTags[] = $metacodeMarker;
        } else {
            if (!HtmlBBCodeParser::getInstance()->isValidTag(['name' => $name, 'attributes' => $attributes])) {
                return null;
            }

            if (!isset($this->openTagIdentifiers[$name])) {
                $this->openTagIdentifiers[$name] = [];
            }
            $uuid = StringUtil::getUUID();
            $this->openTagIdentifiers[$name][] = [
                'uuid' => $uuid,
                'metacodeMarker' => $metacodeMarker
            ];

            $metacodeMarker->setAttribute('data-uuid', $uuid);

            foreach ($bbcode->getAttributes() as $attribute) {
                if ($attribute->useText && !isset($attributes[$attribute->attributeNo])) {
                    $metacodeMarker->setAttribute('data-use-text', $attribute->attributeNo);
                    $this->useTextNodes[] = [
                        'uuid' => $uuid,
                        'metacodeMarker' => $metacodeMarker,
                        'attributeNo' => $attribute->attributeNo,
                    ];
                    break;
                }
            }

            if ($attributes !== []) {
                $metacodeMarker->setAttribute(
                    'data-attributes',
                    \base64_encode(JSON::encode(\array_map(static function ($attribute) {
                        if (\preg_match('~^([\'"])(?P<content>.*)(\1)$~', $attribute, $matches)) {
                            return $matches['content'];
                        }

                        return $attribute;
                    }, $attributes)))
                );
            }
        }

        return $metacodeMarker;
    }

    private function insertBBCode(\DOMElement $node): void
    {
        $node->parentNode->replaceChild(
            $this->document->createTextNode(\base64_decode($node->getAttribute('data-source'))),
            $node
        );
    }
}
