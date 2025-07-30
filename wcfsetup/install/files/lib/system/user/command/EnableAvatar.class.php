<?php

namespace wcf\system\user\command;

use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\event\user\UserAvatarEnabled;
use wcf\system\event\EventHandler;

/**
 * Enable a user's avatar.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class EnableAvatar
{
    public function __construct(
        private readonly User $user,
    ) {
    }

    public function __invoke(): void
    {
        (new UserEditor($this->user))->update([
            'disableAvatar' => 0,
        ]);

        $event = new UserAvatarEnabled($this->user);
        EventHandler::getInstance()->fire($event);
    }
}
