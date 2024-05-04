<?php

namespace wcf\data\file\temporary;

use wcf\data\DatabaseObject;
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

    public const MAX_CHUNK_COUNT = 255;

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
        return \ceil($fileSize / self::getOptimalChunkSize());
    }

    private static function getOptimalChunkSize(): int
    {
        $postMaxSize = \ini_parse_quantity(\ini_get('post_max_size'));
        if ($postMaxSize === 0) {
            // Disabling it is fishy, assume a more reasonable limit of 100 MB.
            $postMaxSize = 100 * 1_024 * 1_024;
        }

        return $postMaxSize;
    }
}
