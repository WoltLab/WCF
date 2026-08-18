<?php

namespace wcf\system\html\metacode\converter;

use wcf\util\StringUtil;

/**
 * Converts quote bbcode into `<woltlab-quote>`.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       3.0
 */
class QuoteMetacodeConverter extends AbstractMetacodeConverter
{
    /**
     * @inheritDoc
     */
    public function convert(\DOMDocumentFragment $fragment, array $attributes)
    {
        $element = $fragment->ownerDocument->createElement('woltlab-quote');
        $element->setAttribute('data-author', isset($attributes[0]) ? StringUtil::decodeHTML($attributes[0]) : '');

        // This attribute is declared as an `URI` in `MessageHtmlInputFilter`, but the
        // bbcode is evaluated after HTMLPurifier has run, therefore the scheme must be
        // validated here to uphold the same guarantee.
        $link = isset($attributes[1]) ? StringUtil::decodeHTML($attributes[1]) : '';
        $element->setAttribute('data-link', UrlMetacodeConverter::hasAllowedScheme($link) ? $link : '');
        $element->appendChild($fragment);

        return $element;
    }

    /**
     * @inheritDoc
     */
    public function validateAttributes(array $attributes)
    {
        // 0, 1 or 2 attributes
        return \count($attributes) <= 2;
    }
}
