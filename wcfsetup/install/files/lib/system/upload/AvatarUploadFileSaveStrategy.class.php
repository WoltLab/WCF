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
     * Reject the file if it is larger than 750 kB after resizing. A worst-case
     * completely-random 128x128 PNG is around 35 kB and JPEG is around 50 kB.
     *
     * Animated GIFs can be much larger depending on the length of animation,
     * 750 kB seems to be a reasonable upper bound for anything that can be
     * considered reasonable with regard to "distraction" and mobile data
     * volume.
     */
    private const MAXIMUM_FILESIZE = 750_000;

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

            if (\filesize($fileLocation) > self::MAXIMUM_FILESIZE) {
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
