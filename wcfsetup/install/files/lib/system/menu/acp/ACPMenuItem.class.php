<?php

namespace wcf\system\menu\acp;

use wcf\system\menu\ITreeMenuItem;
use wcf\system\style\FontAwesomeIcon;
use wcf\system\WCF;

/**
 * Represents an acp menu item.
 *
 * @author      Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class ACPMenuItem implements ITreeMenuItem
{
    public function __construct(
        public readonly string $menuItem,
        public readonly string $title = '',
        public readonly string $parentMenuItem = '',
        public readonly string $link = '',
        public readonly string $icon = ''
    ) {
    }

    public function getLink()
    {
        return $this->link;
    }

    public function getIcon(): ?FontAwesomeIcon
    {
        if ($this->icon) {
            if (FontAwesomeIcon::isValidString($this->icon)) {
                return FontAwesomeIcon::fromString($this->icon);
            } elseif (\str_starts_with($this->icon, 'fa-')) {
                // Safeguard to prevent legacy icons from breaking
                // the admin panel during the upgrade to 6.0.
                return FontAwesomeIcon::fromString("question;true");
            }
        }

        return null;
    }

    public function __toString()
    {
        return $this->title ?: WCF::getLanguage()->get($this->menuItem);
    }
}
