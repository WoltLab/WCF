<?php

namespace wcf\system\user\authentication;

use wcf\data\user\User;
use wcf\system\event\IEvent;
use wcf\system\user\multifactor\Setup;

/**
 * Indicates that the user successfully performed MF authentication with the given setup ("method").
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\User\Authentication
 * @since   5.5
 */
final class UserMultifactorSucceeded implements IEvent
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var Setup
     */
    private $setup;

    public function __construct(User $user, Setup $setup)
    {
        $this->user = $user;
        $this->setup = $setup;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getSetup(): Setup
    {
        return $this->setup;
    }
}
