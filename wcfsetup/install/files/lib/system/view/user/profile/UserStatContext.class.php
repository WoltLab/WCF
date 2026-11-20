<?php

namespace wcf\system\view\user\profile;

/**
 * Identifies the location in which the statistic items of a user are displayed.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
enum UserStatContext: string
{
    case ProfileHeader = 'profileHeader';
    case UserCard = 'userCard';
    case MessageSidebar = 'messageSidebar';
}
