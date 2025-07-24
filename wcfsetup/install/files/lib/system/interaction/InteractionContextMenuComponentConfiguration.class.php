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
        public readonly string $cssClassName = '',
        public readonly string $buttonCssClassName = '',
        public readonly string $dropdownMenuCssClassName = '',
        public readonly string $label = '',
        public readonly string $tooltip = 'wcf.global.button.moreOptions',
        public readonly string $icon = 'ellipsis',
        public readonly int $iconSize = 16
    ) {}

    public static function forDefault(): static
    {
        return new static(buttonCssClassName: 'button small');
    }
}
