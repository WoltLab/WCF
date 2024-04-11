<?php

namespace wcf\system\bbcode;

use wcf\data\bbcode\attribute\BBCodeAttribute;
use wcf\data\bbcode\BBCode;
use wcf\data\bbcode\BBCodeCache;
use wcf\system\SingletonFactory;
use wcf\util\DOMUtil;
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
     * @var string[][]
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
                continue;
            }
            $node->setAttribute('data-uuid', \array_pop($this->openTagIdentifiers[$name]));
        }

        // get text between opening and closing tags
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
    }

    private function convertBBCodeToMetacodeMarker(\DOMNode $node): void
    {
        if (\in_array($node->nodeName, HtmlBBCodeParser::$codeTagNames)) {
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

            DOMUtil::insertBefore($metaCodeMarker, $bbcodeNode);
            $bbcodeNode->parentNode->removeChild($bbcodeNode);
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
                $attributes = $this->buildTagAttributes($match[2]);
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
            if (!$this->isValidTag($bbcode, $attributes)) {
                return null;
            }

            if (!isset($this->openTagIdentifiers[$name])) {
                $this->openTagIdentifiers[$name] = [];
            }
            $uuid = StringUtil::getUUID();
            $this->openTagIdentifiers[$name][] = $uuid;

            $metacodeMarker->setAttribute('data-uuid', $uuid);

            foreach ($bbcode->getAttributes() as $attribute) {
                if ($attribute->useText && !isset($attributes[$attribute->attributeNo])) {
                    $metacodeMarker->setAttribute('data-use-text', 'true');
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

    /**
     * @see BBCodeParser::buildTagAttributes()
     */
    private function buildTagAttributes(string $string): array
    {
        \preg_match_all("~(?:^|,)('[^'\\\\]*(?:\\\\.[^'\\\\]*)*'|[^,]*)~", $string, $matches);

        // remove quotes
        for ($i = 0, $j = \count($matches[1]); $i < $j; $i++) {
            if (\mb_substr($matches[1][$i], 0, 1) == "'" && \mb_substr($matches[1][$i], -1) == "'") {
                $matches[1][$i] = \str_replace("\\'", "'", $matches[1][$i]);
                $matches[1][$i] = \str_replace("\\\\", "\\", $matches[1][$i]);

                $matches[1][$i] = \mb_substr($matches[1][$i], 1, -1);
            }
        }

        return $matches[1];
    }

    /**
     * @see BBCodeParser::isValidTag()
     */
    private function isValidTag(BBCode $bbcode, array $attributes): bool
    {
        if (\count($attributes) > \count($bbcode->getAttributes())) {
            return false;
        }

        // right trim any attributes that are truly empty (= zero-length string) and are defined to be optional
        $bbcodeAttributes = $bbcode->getAttributes();
        // reverse sort the bbcode attributes to start with the last attribute
        \usort($bbcodeAttributes, static function (BBCodeAttribute $a, BBCodeAttribute $b) {
            if ($a->attributeNo == $b->attributeNo) {
                return 0;
            }

            return ($a->attributeNo < $b->attributeNo) ? 1 : -1;
        });
        foreach ($bbcodeAttributes as $attribute) {
            if ($attribute->required) {
                break;
            }

            $i = $attribute->attributeNo;
            if (isset($tagAttributes[$i]) && $tagAttributes[$i] === '' && !isset($tagAttributes[$i + 1])) {
                unset($tagAttributes[$i]);
            } else {
                break;
            }
        }

        foreach ($bbcode->getAttributes() as $attribute) {
            if (!$this->isValidTagAttribute($attributes, $attribute)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @see HtmlBBCodeParser::isValidTagAttribute()
     */
    protected function isValidTagAttribute(array $tagAttributes, BBCodeAttribute $definedTagAttribute): bool
    {
        // work-around for the broken `[wsm]` conversion in earlier versions
        static $targetAttribute;
        if ($targetAttribute === null) {
            $bbcodes = BBCodeHandler::getInstance()->getBBCodes();
            foreach ($bbcodes as $bbcode) {
                if ($bbcode->bbcodeTag === 'wsm') {
                    $targetAttribute = false;
                    foreach ($bbcode->getAttributes() as $attribute) {
                        if ($attribute->attributeNo == 1) {
                            $targetAttribute = $attribute;
                        }
                    }

                    break;
                }
            }
        }
        if ($targetAttribute && $definedTagAttribute === $targetAttribute) {
            if (isset($tagAttributes[1]) && $tagAttributes[1] === '') {
                // allow the 2nd attribute of `[wsm]` to be empty for compatibility reasons
                return true;
            }
        }

        if ($definedTagAttribute->validationPattern && isset($tagAttributes[$definedTagAttribute->attributeNo])) {
            // validate attribute
            if (
                !\preg_match(
                    '~' . \str_replace('~', '\~', $definedTagAttribute->validationPattern) . '~i',
                    $tagAttributes[$definedTagAttribute->attributeNo]
                )
            ) {
                return false;
            }
        }

        if ($definedTagAttribute->required && !$definedTagAttribute->useText && !isset($tagAttributes[$definedTagAttribute->attributeNo])) {
            return false;
        }

        return true;
    }
}
