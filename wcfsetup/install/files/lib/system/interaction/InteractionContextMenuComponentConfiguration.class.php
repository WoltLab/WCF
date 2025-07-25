<?php

namespace wcf\system\interaction;

use wcf\system\style\FontAwesomeIcon;
use wcf\system\style\IFontAwesomeIcon;

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
    public readonly IFontAwesomeIcon $icon;

    public function __construct(
        public readonly string $cssClassName = '',
        public readonly string $buttonCssClassName = '',
        public readonly string $dropdownMenuCssClassName = '',
        public readonly string $label = '',
        public readonly string $tooltip = 'wcf.global.button.moreOptions',
        ?IFontAwesomeIcon $icon = null,
        public readonly int $iconSize = 16
    ) {
        $this->icon = $icon ?? FontAwesomeIcon::fromValues('ellipsis');
    }

    public function getIconHtml(): string
    {
        return $this->icon->toHtml($this->iconSize);
    }

    public static function forDefault(): static
    {
        return new static(buttonCssClassName: 'button small');
    }
}
