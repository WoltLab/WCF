<?php

namespace wcf\data\unfurl\url\image;

use wcf\data\DatabaseObjectCollection;
use wcf\data\file\File;
use wcf\system\cache\runtime\FileRuntimeCache;

/**
 * Represents a collection of unfurled url images.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectCollection<UnfurlUrlImage>
 */
class UnfurlUrlImageCollection extends DatabaseObjectCollection
{
    private bool $filesCached = false;

    public function getFile(UnfurlUrlImage $object): ?File
    {
        $this->cacheFiles();

        return FileRuntimeCache::getInstance()->getObject($object->fileID);
    }

    private function cacheFiles(): void
    {
        if ($this->filesCached) {
            return;
        }

        $this->filesCached = true;

        $fileIDs = \array_unique(\array_map(
            fn($object) => $object->fileID,
            \array_filter(
                $this->getObjects(),
                fn($object) => $object->fileID !== null
            )
        ));
        if ($fileIDs === []) {
            return;
        }

        FileRuntimeCache::getInstance()->cacheObjectIDs($fileIDs);
    }
}
