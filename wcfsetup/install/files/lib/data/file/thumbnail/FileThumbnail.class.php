<?php

namespace wcf\data\file\thumbnail;

use wcf\data\DatabaseObject;

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
 */
class FileThumbnail extends DatabaseObject
{
    public function getPath(): string
    {
        $folderA = \substr($this->fileHash, 0, 2);
        $folderB = \substr($this->fileHash, 2, 2);

        return \sprintf(
            \WCF_DIR . '_data/public/thumbnail/%s/%s/',
            $folderA,
            $folderB,
        );
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
}
