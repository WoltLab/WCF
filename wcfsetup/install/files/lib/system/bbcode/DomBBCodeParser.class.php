<?php

namespace wcf\system\bbcode;

use wcf\data\bbcode\BBCodeCache;
use wcf\system\SingletonFactory;
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
     * @var list<\DOMElement|null>
     */
    private array $bbcodesByAppearance = [];

    /**
     * Parses bbcodes in the given DOM document.
     */
    public function parse(\DOMDocument $document): void
    {
        $this->closingTags = $this->useTextNodes = $this->bbcodesByAppearance = [];
        $this->document = $document;
        foreach ($document->getElementsByTagName('body')->item(0)->childNodes as $node) {
            $this->convertBBCodeToMetacodeMarker($node);
        }

        // Match the opening and closing tags by finding the closest possible
        // match for each tag pair.
        //
        // [foo][foo][/foo][/bar] -> <foo2><foo1></foo1></foo2>
        // [foo][foo][/foo] -> [foo]<foo1></foo1>
        for ($i = 0, $length = \count($this->bbcodesByAppearance); $i < $length; $i++) {
            $element = $this->bbcodesByAppearance[$i];
            if ($element === null) {
                continue;
            }

            // Ignore any opening tags in this loop, they will be matched with
            // closing tags and any remainders will eventually be converted into
            // their source representation.
            if (!\in_array($element, $this->closingTags, true)) {
                continue;
            }

            $name = $element->getAttribute('data-name');
            $element->removeAttribute('data-name');

            // Find the first matching opening tag that appeared before this.
            for ($j = $i - 1; $j >= 0; $j--) {
                $possibleOpeningTag = $this->bbcodesByAppearance[$j];
                if ($possibleOpeningTag === null || \in_array($possibleOpeningTag, $this->closingTags, true)) {
                    continue;
                }

                if ($possibleOpeningTag->getAttribute('data-name') === $name) {
                    // Copy the UUID and to pair the tags.
                    $element->setAttribute('data-uuid', $possibleOpeningTag->getAttribute('data-uuid'));

                    // Set both elements to `null` to remove them from further
                    // checks.
                    $this->bbcodesByAppearance[$i] = null;
                    $this->bbcodesByAppearance[$j] = null;

                    // Important: This targets the outer loop!
                    continue 2;
                }
            }

            // We did not find any matching opening tag, consider this to be a
            // stray tag and convert it back into its BBCode representation.
            $this->insertBBCode($element);
            $this->bbcodesByAppearance[$i] = null;
        }

        // Any opening tag that has not been matched at this point must be
        // converted into its BBCode representation.
        $strayOpeningTags = \array_filter($this->bbcodesByAppearance);
        foreach ($strayOpeningTags as $element) {
            $this->insertBBCode($element);
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
                $attributes = \json_decode(\base64_decode($node->getAttribute('data-attributes')), true, flags: \JSON_THROW_ON_ERROR);
            } else {
                $attributes = [];
            }
            $attributes[$attributeNo] = $text;
            $node->setAttribute('data-attributes', \base64_encode(\json_encode($attributes, \JSON_THROW_ON_ERROR)));
        }
        foreach ($nodes as $node) {
            $node->parentNode?->removeChild($node);
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

            $this->bbcodesByAppearance[] = $metaCodeMarker;
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
            // @phpstan-ignore argument.type
            if (!HtmlBBCodeParser::getInstance()->isValidTag(['name' => $name, 'attributes' => $attributes])) {
                return null;
            }

            $uuid = StringUtil::getUUID();
            $metacodeMarker->setAttribute('data-uuid', $uuid);

            foreach ($bbcode->getAttributes() as $attribute) {
                if ($attribute->useText && !isset($attributes[$attribute->attributeNo])) {
                    $metacodeMarker->setAttribute('data-use-text', (string)$attribute->attributeNo);
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
                    \base64_encode(\json_encode(\array_map(static function ($attribute) {
                        if (\preg_match('~^([\'"])(?P<content>.*)(\1)$~', $attribute, $matches)) {
                            return $matches['content'];
                        }

                        return $attribute;
                    }, $attributes), \JSON_THROW_ON_ERROR))
                );
            }
        }

        return $metacodeMarker;
    }

    private function insertBBCode(\DOMElement $node): void
    {
        \assert($node->childNodes->length === 0);

        $node->replaceWith(\base64_decode($node->getAttribute('data-source')));
    }
}
