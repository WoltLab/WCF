<?php

namespace wcf\event\user\menu\item;

use wcf\data\user\menu\item\event\UserMenuItemIconResolving;
use wcf\data\user\menu\item\UserMenuItem;
use wcf\event\IPsr14Event;
use wcf\system\style\IFontAwesomeIcon;

/**
 * Resolves the icon of a user menu item.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
final class IconResolving extends UserMenuItemIconResolving implements IPsr14Event
{
    public function __construct(
        public readonly UserMenuItem $userMenuItem,
        public IFontAwesomeIcon $icon
    ) {
    }
}
