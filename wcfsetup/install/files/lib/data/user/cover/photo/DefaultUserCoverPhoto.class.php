<?php

namespace wcf\data\user\cover\photo;

use wcf\system\style\StyleHandler;

/**
 * Represents a default cover photo.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class DefaultUserCoverPhoto implements IUserCoverPhoto
{
    #[\Override]
    public function delete()
    {
        /* NOP */
    }

    #[\Override]
    public function getLocation(?bool $forceWebP = null): string
    {
        return StyleHandler::getInstance()->getStyle()->getCoverPhotoLocation($forceWebP);
    }

    #[\Override]
    public function getURL(?bool $forceWebP = null): string
    {
        return StyleHandler::getInstance()->getStyle()->getCoverPhotoUrl($forceWebP);
    }

    #[\Override]
    public function getFilename(?bool $forceWebP = null): string
    {
        return StyleHandler::getInstance()->getStyle()->getCoverPhoto($forceWebP);
    }

    #[\Override]
    public function getObjectID(): ?int
    {
        return null;
    }

    #[\Override]
    public function getThumbnailURL(string $size = 'small'): string
    {
        return $this->getURL();
    }
}
