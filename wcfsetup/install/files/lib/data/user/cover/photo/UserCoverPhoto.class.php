<?php

namespace wcf\data\user\cover\photo;

use wcf\data\file\File;
use wcf\data\file\FileAction;
use wcf\data\user\User;

/**
 * Represents a user's cover photo.
 *
 * @author      Olaf Braun, Alexander Ebert
 * @copyright   2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class UserCoverPhoto implements IUserCoverPhoto
{
    public const MAX_HEIGHT = 800;

    public const MAX_WIDTH = 2000;

    public const MIN_HEIGHT = 200;

    public const MIN_WIDTH = 500;

    public function __construct(
        protected readonly int $userID,
        protected readonly File $file
    ) {}

    #[\Override]
    public function delete()
    {
        (new FileAction([$this->file], 'delete'))->executeAction();
    }

    #[\Override]
    public function getLocation(?bool $forceWebP = null): string
    {
        return $this->file->getPath();
    }

    #[\Override]
    public function getURL(?bool $forceWebP = null): string
    {
        return $this->file->getFullSizeImageSource();
    }

    #[\Override]
    public function getThumbnailURL(string $size = 'small'): string
    {
        $thumbnail = $this->file->getThumbnail($size);

        return $thumbnail ? $thumbnail->getLink() : $this->getURL();
    }

    #[\Override]
    public function getFilename(?bool $forceWebP = null): string
    {
        return $this->file->filename;
    }

    #[\Override]
    public function getObjectID(): int
    {
        return $this->file->fileID;
    }

    /**
     * Returns the minimum and maximum dimensions for cover photos.
     *
     * @return array{
     *  max: array{height: int, width: int},
     *  min: array{height: int, width: int},
     * }
     */
    public static function getCoverPhotoDimensions(): array
    {
        return [
            'max' => [
                'height' => self::MAX_HEIGHT,
                'width' => self::MAX_WIDTH,
            ],
            'min' => [
                'height' => self::MIN_HEIGHT,
                'width' => self::MIN_WIDTH,
            ],
        ];
    }

    /**
     * Returns the location of a user's cover photo before WCF6.2.
     */
    public static function getLegacyLocation(User $user, bool $forceWebP): ?string
    {
        if (!$user->coverPhotoHash || !$user->coverPhotoExtension) {
            return null;
        }

        return \sprintf(
            '%simages/coverPhotos/%s/%d-%s.%s',
            \WCF_DIR,
            \substr(
                $user->coverPhotoHash,
                0,
                2
            ),
            $user->userID,
            $user->coverPhotoHash,
            $forceWebP ? 'webp' : $user->coverPhotoExtension
        );
    }
}
