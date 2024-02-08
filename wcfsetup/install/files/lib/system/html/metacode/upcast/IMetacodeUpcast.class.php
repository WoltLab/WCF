<?php

namespace wcf\system\html\metacode\upcast;

/**
 * Default interface for metacode upcast.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
interface IMetacodeUpcast
{
    /**
     * Converts a known metacode into the HTML representation for use by CKEditor5.
     *
     * @param \DOMElement $element
     * @param array $attributes list of attributes
     */
    public function upcast(\DOMElement $element, array $attributes): void;

    /**
     * Returns true if the given attributes are valid for this upcast.
     * If not, the metacode is converted to a text node with the bbcode output.
     *
     * @param array $attributes
     * @return bool
     */
    public function hasValidAttributes(array $attributes): bool;

    /**
     * Caches the object for the given attributes.
     *
     * @param array $attributes
     */
    public function cacheObject(array $attributes): void;
}
