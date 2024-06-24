<?php

namespace wcf\data\file\thumbnail;

use wcf\data\DatabaseObject;
use wcf\data\ILinkableObject;
use wcf\system\application\ApplicationHandler;
use wcf\system\file\processor\ThumbnailFormat;
use wcf\system\request\LinkHandler;

/**
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 *
 * @property-read int $thumbnailID
 * @property-read int $fileID
 * @property-read string $identifier
 * @property-read string $fileHash
 * @property-read string $fileExtension
 * @property-read int $width
 * @property-read int $height
 * @property-read ?string $formatChecksum
 */
class FileThumbnail extends DatabaseObject implements ILinkableObject
{
    public function getPath(): string
    {
        return \WCF_DIR . $this->getRelativePath();
    }

    public function getSourceFilename(): string
    {
        return \sprintf(
            '%d-%s.%s',
            $this->thumbnailID,
            $this->fileHash,
            $this->fileExtension,
        );
    }

    public function getLink(): string
    {
        return \sprintf(
            '%s%s%s',
            ApplicationHandler::getInstance()->getWCF()->getPageURL(),
            $this->getRelativePath(),
            $this->getSourceFilename(),
        );
    }

    public function getMimeType(): string
    {
        return 'image/webp';
    }

    public function needsRebuild(ThumbnailFormat $format): bool
    {
        if ($this->formatChecksum !== $format->toChecksum()) {
            return true;
        }

        $fileHash = \hash_file('sha256', $this->getPath() . $this->getSourceFilename());
        if ($this->fileHash !== $fileHash) {
            return true;
        }

        return false;
    }

    private function getRelativePath(): string
    {
        $folderA = \substr($this->fileHash, 0, 2);
        $folderB = \substr($this->fileHash, 2, 2);

        return \sprintf(
            '_data/public/thumbnail/%s/%s/',
            $folderA,
            $folderB,
        );
    }
}
