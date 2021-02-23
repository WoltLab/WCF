<?php

namespace wcf\data\user\avatar;

/**
 * Wraps avatars to provide compatibility layers.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\User\Avatar
 */
final class AvatarDecorator implements IUserAvatar, ISafeFormatAvatar
{
    /**
     * @var IUserAvatar
     */
    private $avatar;

    public function __construct(IUserAvatar $avatar)
    {
        $this->avatar = $avatar;
    }

    /**
     * @inheritDoc
     */
    public function getSafeURL(?int $size = null): string
    {
        if ($this->avatar instanceof ISafeFormatAvatar) {
            return $this->avatar->getSafeURL($size);
        }

        return $this->avatar->getURL($size);
    }

    /**
     * @inheritDoc
     */
    public function getSafeImageTag(?int $size = null): string
    {
        if ($this->avatar instanceof ISafeFormatAvatar) {
            return $this->avatar->getSafeImageTag($size);
        }

        return $this->avatar->getImageTag($size);
    }

    /**
     * @inheritDoc
     */
    public function getURL($size = null)
    {
        return $this->avatar->getURL();
    }

    /**
     * @inheritDoc
     */
    public function getImageTag($size = null)
    {
        return $this->avatar->getImageTag($size);
    }

    /**
     * @inheritDoc
     */
    public function getWidth()
    {
        return $this->avatar->getWidth();
    }

    /**
     * @inheritDoc
     */
    public function getHeight()
    {
        return $this->avatar->getHeight();
    }
}
