<?php

namespace wcf\data\file;

use wcf\data\DatabaseObject;

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
            $this->filename,
        );
    }
}
