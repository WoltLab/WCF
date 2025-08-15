<?php

namespace wcf\command\user;

use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\event\user\UserCoverPhotoEnabled;
use wcf\system\event\EventHandler;

/**
 * Enable a user's cover photo.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class EnableCoverPhoto
{
    public function __construct(
        private readonly User $user,
    ) {}

    public function __invoke(): void
    {
        (new UserEditor($this->user))->update([
            'disableCoverPhoto' => 0,
        ]);

        $event = new UserCoverPhotoEnabled($this->user);
        EventHandler::getInstance()->fire($event);
    }
}
