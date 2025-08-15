<?php

namespace wcf\command\user;

use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\event\user\UserAvatarDisabled;
use wcf\system\event\EventHandler;

/**
 * Disable a user's avatar.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class DisableAvatar
{
    public function __construct(
        private readonly User $user,
        private readonly string $reason,
        private readonly int $expires = 0,
    ) {}

    public function __invoke(): void
    {
        (new UserEditor($this->user))->update([
            'disableAvatar' => 1,
            'disableAvatarReason' => $this->reason,
            'disableAvatarExpires' => $this->expires,
        ]);

        $event = new UserAvatarDisabled($this->user);
        EventHandler::getInstance()->fire($event);
    }
}
