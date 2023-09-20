<?php

namespace wcf\data\user\menu\item\event;

use wcf\data\user\menu\item\UserMenuItem;
use wcf\system\event\IEvent;
use wcf\system\style\IFontAwesomeIcon;

/**
 * Resolves the icon of a user menu item.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class UserMenuItemIconResolving implements IEvent
{
    public ?IFontAwesomeIcon $icon = null;

    public function __construct(public readonly UserMenuItem $userMenuItem)
    {
    }
}
