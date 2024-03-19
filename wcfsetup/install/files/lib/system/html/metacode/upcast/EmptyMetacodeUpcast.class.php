<?php

namespace wcf\system\html\metacode\upcast;

/**
 * Always forces the passed BBCode to be converted into its plain text representation.
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
