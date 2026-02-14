<?php

namespace wcf\command\user;

use wcf\data\file\File;
use wcf\data\file\FileAction;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\event\user\CoverPhotoChanged;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\event\EventHandler;

/**
 * Sets the cover photo of a user.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class SetCoverPhoto
{
    public function __construct(
        private readonly User $user,
        private readonly ?File $file = null
    ) {}

    public function __invoke(): void
    {
        if ($this->file === null && $this->user->coverPhotoFileID !== null) {
            (new FileAction([$this->user->coverPhotoFileID], 'delete'))->executeAction();
        }

        (new UserEditor($this->user))->update([
            'coverPhotoFileID' => $this->file?->fileID,
            'coverPhotoHash' => null,
            'coverPhotoExtension' => '',
            'coverPhotoHasWebP' => 0,
        ]);
        UserProfileRuntimeCache::getInstance()->removeObject($this->user->userID);

        EventHandler::getInstance()->fire(new CoverPhotoChanged($this->user, $this->file));
    }
}
