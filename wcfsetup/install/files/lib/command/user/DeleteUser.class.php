<?php

namespace wcf\command\user;

use wcf\command\file\DeleteFiles;
use wcf\data\file\File;
use wcf\data\user\User;
use wcf\data\user\UserBuilder;
use wcf\event\user\UserDeleted;
use wcf\system\attachment\AttachmentHandler;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\comment\CommentHandler;
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
        $this->cleanup($this->user);

        UserBuilder::delete($this->user);

        EventHandler::getInstance()->fire(new UserDeleted($this->user));
    }

    private function cleanup(User $user): void
    {
        ClipboardHandler::getInstance()->unmark(
            [$user->userID],
            ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.user')
        );

        $fileIDs = \array_values(\array_filter([$user->avatarFileID, $user->coverPhotoFileID]));
        if ($fileIDs !== []) {
            $files = File::findByIDs($fileIDs);
            if ($files !== []) {
                new DeleteFiles(\array_values($files))();
            }
        }

        CommentHandler::getInstance()->deleteObjects(
            'com.woltlab.wcf.user.profileComment',
            [$user->userID]
        );

        AttachmentHandler::removeAttachments(
            'com.woltlab.wcf.user.signature',
            [$user->userID]
        );
    }
}
