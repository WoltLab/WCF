<?php

namespace wcf\system\html\metacode\converter;

use wcf\util\StringUtil;

/**
 * Converts spoiler bbcode into `<woltlab-spoiler>`.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       3.0
 */
class SpoilerMetacodeConverter extends AbstractMetacodeConverter
{
    /**
     * @inheritDoc
     */
    public function convert(\DOMDocumentFragment $fragment, array $attributes)
    {
        $element = $fragment->ownerDocument->createElement('woltlab-spoiler');
        $element->setAttribute(
            'data-label',
            (!empty($attributes[0])) ? StringUtil::trim(StringUtil::decodeHTML($attributes[0])) : ''
        );
        $element->appendChild($fragment);

        return $element;
    }

    /**
     * @inheritDoc
     */
    public function validateAttributes(array $attributes)
    {
        // 0 or 1 attribute
        return \count($attributes) <= 1;
    }
}
