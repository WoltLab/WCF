<?php

namespace wcf\system\html\metacode\converter;

/**
 * Converts the mark bbcode into `<mark>`.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class MarkMetacodeConverter extends AbstractMetacodeConverter
{
    /**
     * @inheritDoc
     */
    public function convert(\DOMDocumentFragment $fragment, array $attributes)
    {
        $element = $fragment->ownerDocument->createElement('mark');
        $element->setAttribute('class', $attributes[0]);
        $element->appendChild($fragment);

        return $element;
    }

    /**
     * @inheritDoc
     */
    public function validateAttributes(array $attributes)
    {
        if (\count($attributes) !== 1) {
            return false;
        }

        return true;
    }
}
