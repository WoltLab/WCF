<?php

namespace wcf\data\user\avatar;

use wcf\system\file\processor\UserAvatarFileProcessor;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Constructs an avatar from a static filepath.
 *
 * @author Alexander Ebert
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
final class StaticAvatar implements IUserAvatar, ISafeFormatAvatar
{
    private readonly string $src;

    public function __construct(string $pathname)
    {
        $this->src = WCF::getPath() . $pathname;
    }

    #[\Override]
    public function getImageTag($size = null)
    {
        if ($size === null) {
            $size = UserAvatarFileProcessor::AVATAR_SIZE;
        }

        return '<img src="' . StringUtil::encodeHTML($this->getURL($size)) . '" width="' . $size . '" height="' . $size . '" alt="" class="userAvatarImage">';
    }

    #[\Override]
    public function getSafeURL(?int $size = null): string
    {
        return $this->getURL($size);
    }

    #[\Override]
    public function getSafeImageTag(?int $size = null): string
    {
        return '<img src="' . StringUtil::encodeHTML($this->getSafeURL($size)) . '" width="' . $size . '" height="' . $size . '" alt="" class="userAvatarImage">';
    }

    #[\Override]
    public function getURL($size = null)
    {
        return $this->src;
    }

    #[\Override]
    public function getHeight()
    {
        return UserAvatarFileProcessor::AVATAR_SIZE;
    }

    #[\Override]
    public function getWidth()
    {
        return UserAvatarFileProcessor::AVATAR_SIZE;
    }
}
