<?php

namespace wcf\data;

use wcf\data\file\File;
use wcf\system\cache\runtime\FileRuntimeCache;

/**
 * Trait for dbo collections with files.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
trait TCollectionFiles
{
    private bool $filesCached = false;

    public function getFile(
        DatabaseObject $object,
        string $fileIdProperty = 'fileID',
    ): ?File {
        $this->cacheFiles($fileIdProperty);

        $fileID = $object->{$fileIdProperty};
        if ($fileID === null) {
            return null;
        }

        return FileRuntimeCache::getInstance()->getObject($fileID);
    }

    private function cacheFiles(string $fileIdProperty): void
    {
        if ($this->filesCached) {
            return;
        }

        $this->filesCached = true;

        $fileIDs = $this->getFileIDs($fileIdProperty);
        if ($fileIDs === []) {
            return;
        }

        FileRuntimeCache::getInstance()->cacheObjectIDs($fileIDs);
    }

    /**
     * @return int[]
     */
    private function getFileIDs(string $fileIdProperty): array
    {
        \assert($this instanceof DatabaseObjectCollection);

        return \array_map(
            static fn(DatabaseObject $object) => $object->{$fileIdProperty},
            \array_filter($this->getObjects(), static fn(DatabaseObject $object) => $object->{$fileIdProperty} !== null)
        );
    }
}
