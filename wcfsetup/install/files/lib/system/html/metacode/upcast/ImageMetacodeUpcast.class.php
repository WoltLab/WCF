<?php

namespace wcf\system\html\metacode\upcast;

use wcf\util\DOMUtil;

/**
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
abstract class ImageMetacodeUpcast implements IMetacodeUpcast
{
    /**
     * Create the figure element for the image.
     *
     * @param \DOMElement $element
     * @param \DOMElement $imgElement
     * @param string $alignment
     * @param \DOMElement|null $parentLink
     */
    protected function createFigure(
        \DOMElement $element,
        \DOMElement $imgElement,
        string $alignment,
        ?\DOMElement $parentLink
    ): void {
        $figure = $element->ownerDocument->createElement('figure');
        if ($alignment === 'left') {
            $figure->setAttribute('class', 'image image-style-side-left');
        } elseif ($alignment === 'right') {
            $figure->setAttribute('class', 'image image-style-side');
        } else {
            $figure->setAttribute('class', 'image');
        }
        if ($parentLink !== null) {
            DOMUtil::replaceElement($parentLink, $figure, false);
            $figure->appendChild($parentLink);
            foreach (DomUtil::getChildNodes($parentLink) as $child) {
                $parentLink->removeChild($child);
            }
            $parentLink->appendChild($imgElement);
        } else {
            $figure->appendChild($imgElement);
            DOMUtil::replaceElement($element, $figure, false);
        }
    }
}
