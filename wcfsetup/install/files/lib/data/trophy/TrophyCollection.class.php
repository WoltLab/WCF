<?php

namespace wcf\data\trophy;

use wcf\data\DatabaseObjectCollection;
use wcf\data\file\File;
use wcf\system\cache\runtime\FileRuntimeCache;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @extends DatabaseObjectCollection<Trophy>
 */
final class TrophyCollection extends DatabaseObjectCollection
{
    private bool $filesLoaded = false;

    /**
     * @param Trophy[] $objects
     */
    public function withTrophies(array $objects): self
    {
        return new self(\array_unique(\array_merge($this->getObjects(), $objects)));
    }

    public function getFile(Trophy $trophy): ?File
    {
        $this->loadFiles();

        return FileRuntimeCache::getInstance()->getObject($trophy->imageFileID);
    }

    private function loadFiles(): void
    {
        if ($this->filesLoaded) {
            return;
        }

        $this->filesLoaded = true;

        $fileIDs = [];
        foreach ($this->getObjects() as $object) {
            if ($object->imageFileID) {
                $fileIDs[] = $object->imageFileID;
            }
        }

        if ($fileIDs !== []) {
            FileRuntimeCache::getInstance()->cacheObjectIDs($fileIDs);
        }
    }
}
