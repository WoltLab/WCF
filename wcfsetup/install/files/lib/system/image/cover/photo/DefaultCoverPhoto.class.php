<?php

namespace wcf\system\image\cover\photo;

use wcf\system\style\StyleHandler;
use wcf\util\FileUtil;

/**
 * Represents the default cover photo as defined in the style configuration.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class DefaultCoverPhoto implements ICoverPhoto
{
    private static DefaultCoverPhoto $defaultCoverPhoto;

    /**
     * @var array{height: int, width: int}
     */
    private array $dimensions;

    private function __construct() {}

    #[\Override]
    public function getUrl(?string $size = null): string
    {
        return StyleHandler::getInstance()->getStyle()->getCoverPhotoUrl();
    }

    #[\Override]
    public function getWidth(?string $size = null): int
    {
        return $this->getDimensions()['width'];
    }

    #[\Override]
    public function getHeight(?string $size = null): int
    {
        return $this->getDimensions()['height'];
    }

    #[\Override]
    public function getFileSize(?string $size = null): int
    {
        return \filesize(StyleHandler::getInstance()->getStyle()->getCoverPhotoLocation());
    }

    #[\Override]
    public function getMimeType(?string $size = null): string
    {
        return FileUtil::getMimeType(StyleHandler::getInstance()->getStyle()->getCoverPhotoLocation());
    }

    /**
     * @return array{height: int, width: int}
     */
    private function getDimensions(): array
    {
        if (!isset($this->dimensions)) {
            $this->dimensions = ['height' => 0, 'width' => 0];
            $dimensions = @\getimagesize(
                StyleHandler::getInstance()->getStyle()->getCoverPhotoLocation()
            );
            if (\is_array($dimensions)) {
                $this->dimensions['width'] = $dimensions[0];
                $this->dimensions['height'] = $dimensions[1];
            }
        }

        return $this->dimensions;
    }

    public static function getDefaultCoverPhoto(): self
    {
        if (!isset(self::$defaultCoverPhoto)) {
            self::$defaultCoverPhoto = new self();
        }

        return self::$defaultCoverPhoto;
    }
}
