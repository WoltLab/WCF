<?php

namespace wcf\system\user\authentication;

use wcf\data\user\User;
use wcf\system\event\IEvent;

/**
 * Indicates that the user logged in.
 *
 * Differs from SessionHandler::changeUser() in that it is fired for active logins only and not for user changes
 * that are required for technical reasons.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\User\Authentication
 * @since   5.5
 */
final class UserLoggedIn implements IEvent
{
    /**
     * @var User
     */
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
