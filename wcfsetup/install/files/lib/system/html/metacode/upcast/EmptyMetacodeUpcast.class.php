<?php

namespace wcf\system\html\metacode\upcast;

/**
 * An empty implementation for metacode upcast, that always let the metacdoe convert to his plain text representation.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class EmptyMetacodeUpcast implements IMetacodeUpcast
{
    #[\Override]
    public function upcast(\DOMElement $element, array $attributes): void
    {
        // do nothing
    }

    #[\Override]
    public function hasValidAttributes(array $attributes): bool
    {
        return false;
    }

    #[\Override]
    public function cacheObject(array $attributes): void
    {
        // do nothing
    }
}
