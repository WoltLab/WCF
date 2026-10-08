<?php

namespace wcf\data\user\cover\photo;

use wcf\system\image\cover\photo\generator\CoverPhotoGenerator;
use wcf\system\style\StyleHandler;

/**
 * Represents a default cover photo that is generated for each user individually.
 * Styles with a custom cover photo use it for all users instead.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class GeneratedUserCoverPhoto extends DefaultUserCoverPhoto
{
    private string $url;

    public function __construct(
        private readonly string $username,
    ) {}

    #[\Override]
    public function getURL(?bool $forceWebP = null): string
    {
        // The style is resolved lazily to avoid side effects when only the object is needed.
        if (StyleHandler::getInstance()->getStyle()->coverPhotoExtension !== '') {
            return parent::getURL($forceWebP);
        }

        if (!isset($this->url)) {
            $this->url = CoverPhotoGenerator::forActiveStyle()->getDataUri('user-' . \mb_strtolower($this->username));
        }

        return $this->url;
    }
}
