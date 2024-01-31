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
     *
     * @param \DOMDocumentFragment $fragment fragment containing all child nodes, must be appended to returned element
     * @param array $attributes list of attributes
     * @return  null|\DOMElement     new DOM element
     */
    public function upcast(\DOMDocumentFragment $fragment, array $attributes): ?\DOMElement;

    public function hasValidAttributes(array $attributes): bool;
}
