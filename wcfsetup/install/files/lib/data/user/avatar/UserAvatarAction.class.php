<?php

namespace wcf\data\user\avatar;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\upload\AvatarUploadFileSaveStrategy;
use wcf\system\upload\AvatarUploadFileValidationStrategy;
use wcf\system\upload\UploadFile;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\HTTPRequest;
use wcf\util\ImageUtil;
use wcf\util\Url;

/**
 * Executes avatar-related actions.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\User\Avatar
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
        $saveStrategy = new AvatarUploadFileSaveStrategy((!empty($this->parameters['userID']) ? (int)$this->parameters['userID'] : WCF::getUser()->userID));
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

    /**
     * @deprecated 5.5 Use UserProfileAction::setAvatar() instead.
     */
    public function fetchRemoteAvatar()
    {
        $avatarID = 0;
        $filename = '';

        // fetch avatar from URL
        $imageData = null;
        try {
            $request = new HTTPRequest($this->parameters['url']);
            $request->execute();
            $reply = $request->getReply();
            $filename = FileUtil::getTemporaryFilename('avatar_');
            \file_put_contents($filename, $reply['body']);

            $imageData = \getimagesize($filename);
            if ($imageData === false) {
                throw new SystemException('Downloaded file is not an image');
            }
        } catch (\Exception $e) {
            // log exception unless this was caused by a non-image file being supplied
            if ($imageData !== false) {
                \wcf\functions\exception\logThrowable($e);
            }

            if (!empty($filename)) {
                @\unlink($filename);
            }

            return;
        }

        // rescale avatar if required
        try {
            $newFilename = $this->enforceDimensions($filename);
            if ($newFilename !== $filename) {
                @\unlink($filename);
            }
            $filename = $newFilename;

            $imageData = \getimagesize($filename);
            if ($imageData === false) {
                throw new SystemException('Rescaled file is not an image');
            }
        } catch (\Exception $e) {
            @\unlink($filename);

            return;
        }

        $tmp = Url::parse($this->parameters['url']);
        if (!isset($tmp['path'])) {
            @\unlink($filename);

            return;
        }

        $tmp = \pathinfo($tmp['path']);
        if (!isset($tmp['basename'])) {
            $tmp['basename'] = \basename($filename);
        }

        $imageData = @\getimagesize($filename);
        if ($imageData !== false) {
            $tmp['extension'] = ImageUtil::getExtensionByMimeType($imageData['mime']);

            if (!\in_array($tmp['extension'], ['jpeg', 'jpg', 'png', 'gif', 'webp'])) {
                @\unlink($filename);

                return;
            }
        } else {
            @\unlink($filename);

            return;
        }

        $data = [
            'avatarName' => \mb_substr($tmp['basename'], 0, 255),
            'avatarExtension' => $tmp['extension'],
            'width' => $imageData[0],
            'height' => $imageData[1],
            'userID' => $this->parameters['userEditor']->userID,
            'fileHash' => \sha1_file($filename),
        ];

        // create avatar
        $avatar = UserAvatarEditor::create($data);

        // check avatar directory
        // and create subdirectory if necessary
        $dir = \dirname($avatar->getLocation());
        if (!@\file_exists($dir)) {
            FileUtil::makePath($dir);
        }

        // move uploaded file
        if (@\copy($filename, $avatar->getLocation())) {
            @\unlink($filename);

            $avatarID = $avatar->avatarID;
        } else {
            @\unlink($filename);

            // moving failed; delete avatar
            $editor = new UserAvatarEditor($avatar);
            $editor->delete();
        }

        // update user
        if ($avatarID) {
            /** @var UserEditor $userEditor */
            $userEditor = $this->parameters['userEditor'];

            $userEditor->update([
                'avatarID' => $avatarID,
                'enableGravatar' => 0,
            ]);

            // delete old avatar
            if ($userEditor->avatarID) {
                $action = new self([$userEditor->avatarID], 'delete');
                $action->executeAction();
            }
        }

        // reset user storage
        UserStorageHandler::getInstance()->reset([$this->parameters['userEditor']->userID], 'avatar');
    }

    /**
     * @deprecated 5.5 This is a helper method only used by UserAvatarAction::fetchRemoteAvatar().
     */
    protected function enforceDimensions($filename)
    {
        try {
            $filename = ImageUtil::enforceDimensions($filename, UserAvatar::AVATAR_SIZE, UserAvatar::AVATAR_SIZE);
        } /** @noinspection PhpRedundantCatchClauseInspection */
        catch (SystemException $e) {
            throw new UserInputException('avatar', 'tooLarge');
        }

        return $filename;
    }
}
