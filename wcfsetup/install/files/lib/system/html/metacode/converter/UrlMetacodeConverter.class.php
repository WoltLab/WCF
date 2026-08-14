<?php

namespace wcf\system\html\metacode\converter;

use wcf\util\StringUtil;

/**
 * Converts url bbcode into `<a>`.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       3.0
 */
class UrlMetacodeConverter extends AbstractMetacodeConverter
{
    /**
     * list of allowed schemas as defined by HTMLPurifier
     * @var string[]
     */
    public static $allowedSchemes = ['http', 'https', 'mailto', 'ftp', 'nntp', 'news', 'tel', 'steam', 'ts3server'];

    /**
     * Strips the characters that browsers remove before parsing an url,
     * otherwise a scheme could be masked using control characters, e.g.
     * `java&#9;script:`.
     *
     * @since 6.2
     */
    public static function normalizeUrl(string $url): string
    {
        $url = \preg_replace('~^[\x00-\x20]+|[\x00-\x20]+$~', '', $url);

        return \preg_replace('~[\t\r\n]~', '', $url);
    }

    /**
     * Returns true if the url carries no scheme at all, or one that is part of
     * the list of allowed schemes.
     *
     * @since 6.2
     */
    public static function hasAllowedScheme(string $url): bool
    {
        $url = self::normalizeUrl($url);

        if (\preg_match('~^(?P<scheme>[a-z][a-z0-9+.\-]*):~i', $url, $match)) {
            return \in_array(\mb_strtolower($match['scheme']), self::$allowedSchemes, true);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function convert(\DOMDocumentFragment $fragment, array $attributes)
    {
        $element = $fragment->ownerDocument->createElement('a');

        $href = (!empty($attributes[0])) ? $attributes[0] : '';
        if (empty($href)) {
            $href = $fragment->textContent;
        }

        // The href is not filtered after this point, because the bbcode is
        // evaluated after HTMLPurifier has run. Normalizing the url upfront
        // guarantees that the scheme check below sees the same value as the
        // browser will.
        $href = self::normalizeUrl(StringUtil::decodeHTML($href));

        if (\str_starts_with($href, '//')) {
            // dynamic protocol, treat as https
            $href = "https:{$href}";
        } elseif (\preg_match('~^[a-z][a-z0-9+.\-]*:(?://)?~i', $href, $match)) {
            if (!self::hasAllowedScheme($href)) {
                // invalid schema, replace it with `http`
                $href = 'http://' . \mb_substr($href, \mb_strlen($match[0]));
            }
        } elseif (!\str_contains($href, 'index.php')) {
            // unless it's a relative `index.php` link, assume it is missing the protocol
            $href = "http://{$href}";
        }

        // check if the link is empty, use the href value instead
        $useHrefAsValue = false;
        if ($fragment->childNodes->length === 0) {
            $useHrefAsValue = true;
        } elseif ($fragment->childNodes->length === 1) {
            $node = $fragment->childNodes->item(0);
            if ($node->nodeType === \XML_TEXT_NODE && StringUtil::trim($node->textContent) === '') {
                $useHrefAsValue = true;
            }
        }

        if ($useHrefAsValue) {
            if ($fragment->childNodes->length === 1) {
                $fragment->removeChild($fragment->childNodes->item(0));
            }

            $fragment->appendChild($fragment->ownerDocument->createTextNode($href));
        }

        $element->setAttribute('href', $href);
        $element->appendChild($fragment);

        return $element;
    }

    /**
     * @inheritDoc
     */
    public function validateAttributes(array $attributes)
    {
        if (\count($attributes) > 1) {
            return false;
        }

        return true;
    }
}
