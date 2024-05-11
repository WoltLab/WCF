<?php

namespace wcf\event\user\authentication;

use wcf\data\user\User;
use wcf\event\IPsr14Event;
use wcf\system\cache\runtime\UserRuntimeCache;

/**
 * Indicates that the user actively logged in, i.e. that a user change happened in response
 * to a user's request based off proper authentication.
 *
 * This event specifically must not be used if the active user is changed for technical
 * reasons, e.g. when switching back to the real user after executing some logic with
 * guest permissions.
 *
 * @author      Tim Duesterhus
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class UserLoggedIn extends \wcf\system\user\authentication\event\UserLoggedIn implements IPsr14Event
{
    private int $userID;

    public function __construct(User $user)
    {
        $this->userID = (int)$user->userID;
    }

    public function getUser(): User
    {
        return UserRuntimeCache::getInstance()->getObject($this->userID);
    }
}
