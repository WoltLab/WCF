<?php

namespace wcf\system\user\authentication\event;

use wcf\data\user\User;
use wcf\system\cache\runtime\UserRuntimeCache;
use wcf\system\event\IEvent;

/**
 * Indicates that the user actively logged in, i.e. that a user change happened in response
 * to a user's request based off proper authentication.
 *
 * This event specifically must not be used if the active user is changed for technical
 * reasons, e.g. when switching back to the real user after executing some logic with
 * guest permissions.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\User\Authentication\Event
 * @since   5.5
 */
final class UserLoggedIn implements IEvent
{
    /**
     * @var int
     */
    private $userID;

    public function __construct(User $user)
    {
        $this->userID = (int)$user->userID;
    }

    public function getUser(): User
    {
        return UserRuntimeCache::getInstance()->getObject($this->userID);
    }
}
