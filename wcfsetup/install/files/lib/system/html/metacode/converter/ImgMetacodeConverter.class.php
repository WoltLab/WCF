<?php

namespace wcf\system\html\metacode\converter;

use wcf\util\StringUtil;

/**
 * Converts img bbcode into `<img>`.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       3.0
 */
class ImgMetacodeConverter extends AbstractMetacodeConverter
{
    /**
     * @inheritDoc
     */
    public function convert(\DOMDocumentFragment $fragment, array $attributes)
    {
        $element = $fragment->ownerDocument->createElement('img');

        // The src is not filtered after this point, because the bbcode is evaluated
        // after HTMLPurifier has run. Decoding and normalizing the url upfront
        // guarantees that the scheme check in `validateAttributes()` sees the same
        // value as the browser will.
        $element->setAttribute(
            'src',
            UrlMetacodeConverter::normalizeUrl(StringUtil::decodeHTML($attributes[0]))
        );

        if (isset($attributes[1]) && \in_array($attributes[1], ['left', 'right'])) {
            $element->setAttribute('class', 'messageFloatObject' . \ucfirst($attributes[1]));
        }

        return $element;
    }

    /**
     * @inheritDoc
     */
    public function validateAttributes(array $attributes)
    {
        $count = \count($attributes);
        if ($count > 0 && $count < 4) {
            return $this->hasAllowedScheme(StringUtil::decodeHTML($attributes[0]));
        }

        return false;
    }

    /**
     * Returns true if the url carries no scheme at all, or one that an image can
     * actually be fetched from. This is deliberately stricter than
     * `UrlMetacodeConverter::hasAllowedScheme()`, whose list applies to links.
     */
    private function hasAllowedScheme(string $url): bool
    {
        $url = UrlMetacodeConverter::normalizeUrl($url);

        if (\preg_match('~^(?P<scheme>[a-z][a-z0-9+.\-]*):~i', $url, $match)) {
            return \in_array(\mb_strtolower($match['scheme']), ['http', 'https'], true);
        }

        return true;
    }
}
