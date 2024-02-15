<?php

namespace wcf\data\file;

use wcf\action\FileDownloadAction;
use wcf\data\DatabaseObject;
use wcf\system\file\processor\FileProcessor;
use wcf\system\file\processor\IFileProcessor;
use wcf\system\request\LinkHandler;

/**
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 *
 * @property-read int $fileID
 * @property-read string $filename
 * @property-read int $fileSize
 * @property-read string $fileHash
 * @property-read string $typeName
 */
class File extends DatabaseObject
{
    public function getPath(): string
    {
        $folderA = \substr($this->fileHash, 0, 2);
        $folderB = \substr($this->fileHash, 2, 2);

        return \sprintf(
            \WCF_DIR . '_data/public/fileUpload/%s/%s/',
            $folderA,
            $folderB,
        );
    }

    public function getSourceFilename(): string
    {
        return \sprintf(
            '%d-%s.bin',
            $this->fileID,
            $this->fileHash,
        );
    }

    public function getLink(): string
    {
        return LinkHandler::getInstance()->getControllerLink(
            FileDownloadAction::class,
            ['id' => $this->fileID]
        );
    }

    public function getProcessor(): ?IFileProcessor
    {
        return FileProcessor::getInstance()->forTypeName($this->typeName);
    }
}
