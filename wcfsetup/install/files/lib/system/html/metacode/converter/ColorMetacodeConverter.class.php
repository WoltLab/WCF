<?php

namespace wcf\system\html\metacode\converter;

/**
 * Converts color bbcode into `<span style="color: ...">`.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ColorMetacodeConverter extends AbstractMetacodeConverter
{
    #[\Override]
    public function convert(\DOMDocumentFragment $fragment, array $attributes)
    {
        $element = $fragment->ownerDocument->createElement('span');
        $element->setAttribute('style', 'color: ' . $attributes[0]);
        $element->appendChild($fragment);

        return $element;
    }

    #[\Override]
    public function validateAttributes(array $attributes)
    {
        if (\count($attributes) !== 1) {
            return false;
        }

        return true;
    }
}
