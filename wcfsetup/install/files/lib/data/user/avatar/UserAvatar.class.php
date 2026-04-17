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
 *
 * @property-read   int     $avatarID           unique id of the user avatar
 * @property-read   string  $avatarName         name of the original avatar file
 * @property-read   string  $avatarExtension    extension of the avatar file
 * @property-read   int     $width              width of the user avatar image
 * @property-read   int     $height             height of the user avatar image
 * @property-read   ?int    $userID             id of the user to which the user avatar belongs or null
 * @property-read   string  $fileHash           SHA1 hash of the original avatar file
 * @property-read   0|1     $hasWebP            `1` if there is a WebP variant, else `0`
 *
 * @deprecated 6.2
 */
class UserAvatar extends DatabaseObject implements IUserAvatar, ISafeFormatAvatar
{
    /**
     * minimum height and width of an uploaded avatar
     * @var int
     */
    const AVATAR_SIZE = 128;

    /**
     * minimum height and width of an uploaded avatar (HiDPI version)
     * @var int
     */
    const AVATAR_SIZE_2X = 256;

    /**
     * Returns the physical location of this avatar.
     *
     * @return string
     */
    public function getLocation(?int $size = null, ?bool $forceWebP = null)
    {
        return \WCF_DIR . 'images/avatars/' . $this->getFilename($size, $forceWebP);
    }

    /**
     * Returns the file name of this avatar.
     *
     * @return string
     */
    public function getFilename(?int $size = null, ?bool $forceWebP = null)
    {
        if (
            $forceWebP === true
            || ($forceWebP === null && $this->hasWebP && ImageUtil::browserSupportsWebp())
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

    #[\Override]
    public function getURL(?int $size = null)
    {
        return WCF::getPath() . 'images/avatars/' . $this->getFilename();
    }

    #[\Override]
    public function getSafeURL(?int $size = null): string
    {
        return WCF::getPath() . 'images/avatars/' . $this->getFilename(null, false);
    }

    #[\Override]
    public function getImageTag(?int $size = null, bool $lazyLoading = true)
    {
        return \sprintf(
            '<img src="%s" width="%d" height="%d" alt="" class="userAvatarImage" loading="%s">',
            StringUtil::encodeHTML($this->getURL($size)),
            $size,
            $size,
            $lazyLoading ? 'lazy' : 'eager'
        );
    }

    #[\Override]
    public function getSafeImageTag(?int $size = null): string
    {
        return '<img src="' . StringUtil::encodeHTML($this->getSafeURL($size)) . '" width="' . $size . '" height="' . $size . '" alt="" class="userAvatarImage">';
    }

    #[\Override]
    public function getWidth()
    {
        return $this->width;
    }

    #[\Override]
    public function getHeight()
    {
        return $this->height;
    }
}
