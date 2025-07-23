<?php

namespace wcf\system\interaction;

/**
 * Represents the configuraton for a component of an interaction content menu.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class InteractionContextMenuComponentConfiguration
{
    public function __construct(
        public readonly string $cssClassName,
        public readonly string $icon,
        public readonly int $iconSize
    ) {}

    public static function forDefault(): static
    {
        return new static('button small', 'ellipsis', 16);
    }
}
