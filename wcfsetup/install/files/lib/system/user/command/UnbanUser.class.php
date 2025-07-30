<?php

namespace wcf\system\user\command;

use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\event\user\UserUnbanned;
use wcf\system\event\EventHandler;

/**
 * Unban a user.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class UnbanUser
{
    public function __construct(private readonly User $user)
    {
    }

    public function __invoke(): void
    {
        (new UserEditor($this->user))->update([
            'banned' => 0,
            'banExpires' => 0,
        ]);

        $event = new UserUnbanned($this->user);
        EventHandler::getInstance()->fire($event);
    }
}
