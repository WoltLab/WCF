<?php

namespace wcf\data\user\cover\photo;

use wcf\system\WCF;
use wcf\util\ImageUtil;

/**
 * Represents a user's cover photo.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\User\Cover\Photo
 */
class UserCoverPhoto implements IWebpUserCoverPhoto
{
    /**
     * file extension
     * @var string
     */
    protected $coverPhotoExtension;

    /**
     * file hash
     * @var string
     */
    protected $coverPhotoHash;

    /**
     * @var int
     */
    protected $coverPhotoHasWebP = 0;

    /**
     * user id
     * @var int
     */
    protected $userID;

    const MAX_HEIGHT = 800;

    const MAX_WIDTH = 2000;

    const MIN_HEIGHT = 200;

    const MIN_WIDTH = 500;

    /**
     * UserCoverPhoto constructor.
     *
     * @param int $userID
     * @param string $coverPhotoHash
     * @param string $coverPhotoExtension
     */
    public function __construct($userID, $coverPhotoHash, $coverPhotoExtension, int $coverPhotoHasWebP)
    {
        $this->userID = $userID;
        $this->coverPhotoHash = $coverPhotoHash;
        $this->coverPhotoExtension = $coverPhotoExtension;
        $this->coverPhotoHasWebP = $coverPhotoHasWebP;
    }

    /**
     * @inheritDoc
     */
    public function delete()
    {
        if (\file_exists($this->getLocation(false))) {
            @\unlink($this->getLocation(false));
        }

        if (\file_exists($this->getLocation(true))) {
            @\unlink($this->getLocation(true));
        }
    }

    /**
     * @inheritDoc
     */
    public function getLocation(?bool $forceWebP = null): string
    {
        return WCF_DIR . 'images/coverPhotos/' . $this->getFilename($forceWebP);
    }

    /**
     * @inheritDoc
     */
    public function getURL(?bool $forceWebP = null): string
    {
        return WCF::getPath() . 'images/coverPhotos/' . $this->getFilename($forceWebP);
    }

    /**
     * @inheritDoc
     */
    public function getFilename(?bool $forceWebP = null): string
    {
        $useWebP = $forceWebP || ($this->coverPhotoHasWebP && $forceWebP === null && ImageUtil::browserSupportsWebp());

        return \substr(
            $this->coverPhotoHash,
            0,
            2
        ) . '/' . $this->userID . '-' . $this->coverPhotoHash . '.' . ($useWebP ? 'webp' : $this->coverPhotoExtension);
    }

    /**
     * @inheritDoc
     */
    public function createWebpVariant()
    {
        if ($this->coverPhotoHasWebP) {
            return;
        }

        $sourceLocation = $this->getLocation($this->coverPhotoExtension === 'webp');
        $outputFilenameWithoutExtension = \preg_replace('~\.[a-z]+$~', '', $sourceLocation);

        return ImageUtil::createWebpVariant($sourceLocation, $outputFilenameWithoutExtension);
    }

    /**
     * Returns the minimum and maximum dimensions for cover photos.
     *
     * @return      array
     */
    public static function getCoverPhotoDimensions()
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
}
