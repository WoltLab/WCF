<?php

namespace wcf\system\user\command;

use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\event\user\UserBanned;
use wcf\system\event\EventHandler;

/**
 * Ban a user.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class BanUser
{
    public function __construct(
        private readonly User $user,
        private readonly string $reason,
        private readonly int $banExpires = 0,
    ) {
    }

    public function __invoke(): void
    {
        (new UserEditor($this->user))->update([
            'banned' => 1,
            'banReason' => $this->reason,
            'banExpires' => $this->banExpires,
        ]);

        $event = new UserBanned($this->user);
        EventHandler::getInstance()->fire($event);
    }
}
