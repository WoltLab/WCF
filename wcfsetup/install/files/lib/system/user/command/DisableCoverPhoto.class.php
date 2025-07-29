<?php

namespace wcf\system\user\command;

use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\event\user\UserCoverPhotoDisabled;
use wcf\system\event\EventHandler;

/**
 * Disable a user's cover photo.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class DisableCoverPhoto
{
    public function __construct(
        private readonly User $user,
        private readonly string $reason,
        private readonly int $expires = 0,
    ) {
    }

    public function __invoke(): void
    {
        (new UserEditor($this->user))->update([
            'disableCoverPhoto' => 1,
            'disableCoverPhotoReason' => $this->reason,
            'disableCoverPhotoExpires' => $this->expires,
        ]);

        $event = new UserCoverPhotoDisabled($this->user);
        EventHandler::getInstance()->fire($event);
    }
}
