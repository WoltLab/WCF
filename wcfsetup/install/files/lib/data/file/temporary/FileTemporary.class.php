<?php

namespace wcf\data\file\temporary;

use wcf\data\DatabaseObject;
use wcf\system\file\processor\FileProcessor;
use wcf\util\JSON;

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
 * @property-read int|null $objectTypeID
 * @property-read string $context
 * @property-read string $chunks
 */
class FileTemporary extends DatabaseObject
{
    protected static $databaseTableIndexIsIdentity = false;

    protected static $databaseTableIndexName = 'identifier';

    public function getChunkCount(): int
    {
        return \strlen($this->chunks);
    }

    public function getChunkSize(): int
    {
        return \ceil($this->fileSize / $this->getChunkCount());
    }

    public function hasChunk(int $sequenceNo): bool
    {
        if ($sequenceNo > \strlen($this->chunks)) {
            throw new \OutOfRangeException(
                \sprintf(
                    "Cannot access chunk #%d of %d",
                    $sequenceNo,
                    \strlen($this->chunks),
                ),
            );
        }

        return $this->chunks[$sequenceNo] === '1';
    }

    public function getFilename(): string
    {
        return \sprintf("%s.bin", $this->identifier);
    }

    public function getPath(): string
    {
        $folderA = \substr($this->identifier, 0, 2);
        $folderB = \substr($this->identifier, 2, 2);

        return \sprintf(
            \WCF_DIR . '_data/private/fileUpload/%s/%s/',
            $folderA,
            $folderB,
        );
    }

    public function getPathname(): string
    {
        return $this->getPath() . $this->getFilename();
    }

    public function getContext(): array
    {
        return JSON::decode($this->context);
    }

    public static function getNumberOfChunks(int $fileSize): int
    {
        return \ceil($fileSize / FileProcessor::getInstance()->getOptimalChunkSize());
    }
}
