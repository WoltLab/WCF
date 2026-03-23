<?php

namespace wcf\data\user\avatar;

use wcf\data\file\File;
use wcf\system\file\processor\UserAvatarFileProcessor;
use wcf\util\StringUtil;

/**
 * Wraps avatars to provide compatibility layers.
 *
 * @author      Olaf Braun, Tim Duesterhus
 * @copyright   2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class AvatarDecorator implements IUserAvatar, ISafeFormatAvatar
{
    private IUserAvatar | File $avatar;

    public function __construct(IUserAvatar | File $avatar)
    {
        $this->avatar = $avatar;
    }

    /**
     * @inheritDoc
     */
    public function getSafeURL(?int $size = null): string
    {
        if ($this->avatar instanceof File) {
            return $this->getURL($size);
        } elseif ($this->avatar instanceof ISafeFormatAvatar) {
            return $this->avatar->getSafeURL($size);
        }

        return $this->avatar->getURL($size);
    }

    /**
     * @inheritDoc
     */
    public function getSafeImageTag(?int $size = null): string
    {
        if ($this->avatar instanceof File) {
            return $this->getImageTag($size);
        } elseif ($this->avatar instanceof ISafeFormatAvatar) {
            return $this->avatar->getSafeImageTag($size);
        }

        return $this->avatar->getImageTag($size);
    }

    /**
     * @inheritDoc
     */
    public function getURL(?int $size = null)
    {
        if ($this->avatar instanceof File) {
            $thumbnail = $this->avatar->getThumbnail((string)UserAvatarFileProcessor::AVATAR_SIZE_2X)
                ?? $this->avatar->getThumbnail((string)UserAvatarFileProcessor::AVATAR_SIZE);
            if ($thumbnail !== null) {
                return $thumbnail->getLink();
            }

            return $this->avatar->getFullSizeImageSource();
        } else {
            return $this->avatar->getURL();
        }
    }

    /**
     * @inheritDoc
     */
    public function getImageTag(?int $size = null, bool $lazyLoading = true)
    {
        if ($this->avatar instanceof File) {
            return \sprintf(
                '<img src="%s" width="%d" height="%d" alt="" class="userAvatarImage" loading="%s">',
                StringUtil::encodeHTML($this->getSafeURL($size)),
                $size,
                $size,
                $lazyLoading ? 'lazy' : 'eager'
            );
        } else {
            // @phpstan-ignore arguments.count
            return $this->avatar->getImageTag($size, $lazyLoading);
        }
    }

    /**
     * @inheritDoc
     */
    public function getWidth()
    {
        if ($this->avatar instanceof File) {
            return $this->avatar->width;
        } else {
            return $this->avatar->getWidth();
        }
    }

    /**
     * @inheritDoc
     */
    public function getHeight()
    {
        if ($this->avatar instanceof File) {
            return $this->avatar->height;
        } else {
            return $this->avatar->getHeight();
        }
    }
}
