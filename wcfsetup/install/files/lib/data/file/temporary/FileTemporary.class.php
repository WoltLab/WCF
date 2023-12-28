<?php

namespace wcf\data\file\temporary;

use wcf\data\DatabaseObject;

/**
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 *
 * @property-read string $identifier
 * @property-read int|null $time
 * @property-read string $filename
 * @property-read int $fileSize
 * @property-read string $fileHash
 */
class FileTemporary extends DatabaseObject
{
    protected static $databaseTableIndexIsIdentity = false;

    protected static $databaseTableIndexName = 'identifier';

    public function getNumberOfChunks(): int
    {
        return \ceil($this->fileSize / $this->getOptimalChunkSize());
    }

    private function getOptimalChunkSize(): int
    {
        $postMaxSize = \ini_parse_quantity(\ini_get('post_max_size'));
        if ($postMaxSize === 0) {
            // Disabling it is fishy, assume a more reasonable limit of 100 MB.
            $postMaxSize = 100 * 1_024 * 1_024;
        }

        return $postMaxSize;
    }
}
