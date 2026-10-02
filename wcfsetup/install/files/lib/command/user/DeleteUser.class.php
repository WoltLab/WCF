<?php

namespace wcf\command\user;

use wcf\data\user\User;
use wcf\data\user\UserBuilder;
use wcf\event\user\UserDeleted;
use wcf\system\event\EventHandler;

/**
 * Deletes a user.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class DeleteUser
{
    public function __construct(
        private readonly User $user,
    ) {}

    public function __invoke(): void
    {
        UserBuilder::delete($this->user);

        EventHandler::getInstance()->fire(new UserDeleted($this->user));
    }
}
