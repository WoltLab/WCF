<?php

namespace wcf\system\user\authentication;

use wcf\data\user\User;
use wcf\system\event\IEvent;

/**
 * Indicates that the user entered their password successfully and needs to perform MF authentication.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\User\Authentication
 * @since   5.5
 */
final class UserMultifactorRequested implements IEvent
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
