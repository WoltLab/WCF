<?php

namespace wcf\system\upload;

use RuntimeException;
use wcf\data\user\avatar\UserAvatar;
use wcf\data\user\User;
use wcf\data\user\UserProfileAction;
use wcf\system\exception\SystemException;
use wcf\system\WCF;
use wcf\util\ImageUtil;

/**
 * Save strategy for avatar uploads.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Upload
 * @since   5.2
 */
class AvatarUploadFileSaveStrategy implements IUploadFileSaveStrategy
{
    /**
     * @var int
     */
    protected $userID = 0;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var UserAvatar
     */
    protected $avatar;

    /**
     * Creates a new instance of AvatarUploadFileSaveStrategy.
     *
     * @param int $userID
     */
    public function __construct($userID = null)
    {
        $this->userID = ($userID ?: WCF::getUser()->userID);
        $this->user = ($this->userID != WCF::getUser()->userID ? new User($userID) : WCF::getUser());
    }

    /**
     * @return UserAvatar
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * @inheritDoc
     */
    public function save(UploadFile $uploadFile)
    {
        if (!$uploadFile->getValidationErrorType()) {
            // rotate avatar if necessary
            /** @noinspection PhpUnusedLocalVariableInspection */
            $fileLocation = ImageUtil::fixOrientation($uploadFile->getLocation());

            // shrink avatar if necessary
            try {
                $fileLocation = ImageUtil::enforceDimensions(
                    $fileLocation,
                    UserAvatar::AVATAR_SIZE,
                    UserAvatar::AVATAR_SIZE,
                    false
                );
            }
            /** @noinspection PhpRedundantCatchClauseInspection */
            catch (SystemException $e) {
                $uploadFile->setValidationErrorType('tooLarge');

                return;
            }

            // check filesize (after shrink)
            if (@\filesize($fileLocation) > WCF::getSession()->getPermission('user.profile.avatar.maxSize')) {
                $uploadFile->setValidationErrorType('tooLarge');

                return;
            }

            try {
                $returnValues = (new UserProfileAction([$this->userID], 'setAvatar', [
                    'fileLocation' => $fileLocation,
                    'filename' => $uploadFile->getFilename(),
                    'extension' => $uploadFile->getFileExtension(),
                ]))->executeAction();

                $this->avatar = $returnValues['returnValues']['avatar'];
            } catch (RuntimeException $e) {
                $uploadFile->setValidationErrorType('uploadFailed');
            }
        }
    }
}
