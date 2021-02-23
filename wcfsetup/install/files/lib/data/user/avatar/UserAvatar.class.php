<?php

namespace wcf\data\user\avatar;

use wcf\data\DatabaseObject;
use wcf\system\WCF;
use wcf\util\ImageUtil;
use wcf\util\StringUtil;

/**
 * Represents a user's avatar.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\User\Avatar
 *
 * @property-read int $avatarID unique id of the user avatar
 * @property-read string $avatarName name of the original avatar file
 * @property-read string $avatarExtension extension of the avatar file
 * @property-read int $width width of the user avatar image
 * @property-read int $height height of the user avatar image
 * @property-read int|null $userID id of the user to which the user avatar belongs or null
 * @property-read string $fileHash SHA1 hash of the original avatar file
 * @property-read int $hasWebP `1` if there is a WebP variant, else `0`
 */
class UserAvatar extends DatabaseObject implements IUserAvatar, ISafeFormatAvatar
{
    /**
     * needed avatar thumbnail sizes
     * @var int[]
     * @deprecated 3.0
     */
    public static $avatarThumbnailSizes = [32, 96, 128, 256];

    /**
     * maximum thumbnail size
     * @var int
     * @deprecated 3.0
     */
    public static $maxThumbnailSize = 128;

    /**
     * minimum height and width of an uploaded avatar
     * @var int
     * @deprecated 3.0
     */
    const MIN_AVATAR_SIZE = 96;

    /**
     * minimum height and width of an uploaded avatar
     * @var int
     */
    const AVATAR_SIZE = 128;

    /**
     * Returns the physical location of this avatar.
     *
     * @param int $size
     * @param bool|null $forceWebP
     * @return string
     */
    public function getLocation($size = null, ?bool $forceWebP = null)
    {
        return WCF_DIR . 'images/avatars/' . $this->getFilename($size, $forceWebP);
    }

    /**
     * Returns the file name of this avatar.
     *
     * @param int $size
     * @param bool|null $forceWebP
     * @return string
     */
    public function getFilename($size = null, ?bool $forceWebP = null)
    {
        if (
            $forceWebP === true
            || ($forceWebP === null && $this->hasWebP && ImageUtil::browserSupportsWebP())
        ) {
            $fileExtension = "webp";
        } else {
            $fileExtension = $this->avatarExtension;
        }

        $directory = \substr($this->fileHash, 0, 2);

        return \sprintf(
            '%s/%d-%s.%s',
            $directory,
            $this->avatarID,
            $this->fileHash . ($size !== null ? ('-' . $size) : ''),
            $fileExtension
        );
    }

    /**
     * @inheritDoc
     */
    public function getURL($size = null)
    {
        return WCF::getPath() . 'images/avatars/' . $this->getFilename();
    }

    /**
     * @inheritDoc
     */
    public function getSafeURL(?int $size = null): string
    {
        return WCF::getPath() . 'images/avatars/' . $this->getFilename(null, false);
    }

    /**
     * @inheritDoc
     */
    public function getImageTag($size = null)
    {
        return '<img src="' . StringUtil::encodeHTML($this->getURL($size)) . '" width="' . $size . '" height="' . $size . '" alt="" class="userAvatarImage">';
    }

    /**
     * @inheritDoc
     */
    public function getSafeImageTag(?int $size = null): string
    {
        return '<img src="' . StringUtil::encodeHTML($this->getSafeURL($size)) . '" width="' . $size . '" height="' . $size . '" alt="" class="userAvatarImage">';
    }

    /**
     * @inheritDoc
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @inheritDoc
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @deprecated  3.0
     */
    public function getCropImageTag($size = null)
    {
        return '';
    }

    /**
     * @deprecated  3.0
     */
    public function canCrop()
    {
        return false;
    }
}
