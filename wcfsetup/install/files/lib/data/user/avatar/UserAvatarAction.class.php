<?php

namespace wcf\data\user\avatar;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\user\User;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\upload\AvatarUploadFileSaveStrategy;
use wcf\system\upload\AvatarUploadFileValidationStrategy;
use wcf\system\upload\UploadFile;
use wcf\system\WCF;

/**
 * Executes avatar-related actions.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  UserAvatar      create()
 * @method  UserAvatarEditor[]  getObjects()
 * @method  UserAvatarEditor    getSingleObject()
 */
class UserAvatarAction extends AbstractDatabaseObjectAction
{
    /**
     * currently edited avatar
     * @var UserAvatarEditor
     */
    public $avatar;

    /**
     * Validates the upload action.
     */
    public function validateUpload()
    {
        $this->readInteger('userID', true);

        if ($this->parameters['userID']) {
            if (!WCF::getSession()->getPermission('admin.user.canEditUser')) {
                throw new PermissionDeniedException();
            }

            $user = new User($this->parameters['userID']);
            if (!$user->userID) {
                throw new IllegalLinkException();
            }
        }

        // check upload permissions
        if (!WCF::getSession()->getPermission('user.profile.avatar.canUploadAvatar') || WCF::getUser()->disableAvatar) {
            throw new PermissionDeniedException();
        }

        /** @noinspection PhpUndefinedMethodInspection */
        if (\count($this->parameters['__files']->getFiles()) != 1) {
            throw new UserInputException('files');
        }

        // check max filesize, allowed file extensions etc.
        /** @noinspection PhpUndefinedMethodInspection */
        $this->parameters['__files']->validateFiles(new AvatarUploadFileValidationStrategy(
            \PHP_INT_MAX,
            \explode("\n", WCF::getSession()->getPermission('user.profile.avatar.allowedFileExtensions'))
        ));
    }

    /**
     * Handles uploaded attachments.
     */
    public function upload()
    {
        /** @var UploadFile $file */
        $file = $this->parameters['__files']->getFiles()[0];
        $saveStrategy = new AvatarUploadFileSaveStrategy((!empty($this->parameters['userID']) ? \intval($this->parameters['userID']) : WCF::getUser()->userID));
        /** @noinspection PhpUndefinedMethodInspection */
        $this->parameters['__files']->saveFiles($saveStrategy);

        if ($file->getValidationErrorType()) {
            return ['errorType' => $file->getValidationErrorType()];
        } else {
            return [
                'avatarID' => $saveStrategy->getAvatar()->avatarID,
                'url' => $saveStrategy->getAvatar()->getURL(96),
            ];
        }
    }
}
