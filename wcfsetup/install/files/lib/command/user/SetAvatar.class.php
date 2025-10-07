<?php

namespace wcf\command\user;

use wcf\data\file\File;
use wcf\data\file\FileAction;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\user\group\assignment\UserGroupAssignmentHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\user\UserProfileHandler;
use wcf\system\WCF;

/**
 * Sets the avatar of a user.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class SetAvatar
{
    public function __construct(
        private readonly User $user,
        private readonly ?File $file = null
    ) {}

    public function __invoke(): void
    {
        if ($this->file === null && $this->user->avatarFileID !== null) {
            (new FileAction([$this->user->avatarFileID], 'delete'))->executeAction();
        }

        $pathname = null;
        if ($this->file !== null) {
            $filename = $this->file->getSourceFilenameWebp() ?? $this->file->getSourceFilename();
            $pathname = $this->file->getRelativePath() . $filename;
        }

        (new UserEditor($this->user))->update([
            'avatarFileID' => $this->file?->fileID,
            'avatarPathname' => $pathname,
            'avatarID' => null,
        ]);

        UserStorageHandler::getInstance()->reset([$this->user->userID], 'avatar');
        UserProfileRuntimeCache::getInstance()->removeObject($this->user->userID);

        // Setting an avatar could satisfy the condition to assign a user to user groups.
        UserGroupAssignmentHandler::getInstance()->checkUsers([$this->user->userID]);

        if ($this->user->userID === WCF::getUser()->userID) {
            UserProfileHandler::getInstance()->reloadUserProfile();
        }
    }
}
