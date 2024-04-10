<?php

namespace wcf\system\bbcode;

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
    protected array $openTagIdentifiers = [];
    private \DOMDocument $document;

    /**
     * Parses bbcodes in the given DOM document.
     */
    public function parse(\DOMDocument $document): void
    {
        $this->openTagIdentifiers = [];
        $this->document = $document;
        foreach ($document->getElementsByTagName('body')->item(0)->childNodes as $node) {
            $this->convertBBCodeToMetacodeMarker($node);
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
        if ($isClosingTag) {
            if (empty($this->openTagIdentifiers)) {
                return null;
            }

            $openTagIdentifier = \array_pop($this->openTagIdentifiers);
            if ($openTagIdentifier['name'] !== $name) {
                return null;
            }

            $uuid = $openTagIdentifier['uuid'];
        } else {
            $uuid = StringUtil::getUUID();
            $this->openTagIdentifiers[] = [
                'name' => $name,
                'uuid' => $uuid,
            ];

            $metacodeMarker->setAttribute('data-name', $name);
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

            foreach ($bbcode->getAttributes() as $attribute) {
                if ($attribute->useText && !isset($attributes[$attribute->attributeNo])) {
                    $metacodeMarker->setAttribute('data-use-text', 'true');
                    break;
                }
            }
        }
        $metacodeMarker->setAttribute('data-uuid', $uuid);

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
}
