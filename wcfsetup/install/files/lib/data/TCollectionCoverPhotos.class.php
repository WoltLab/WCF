<?php

namespace wcf\data;

use wcf\data\file\FileList;
use wcf\data\file\thumbnail\FileThumbnailList;
use wcf\system\image\cover\photo\FileCoverPhoto;

/**
 * Trait for dbo collections with cover photos.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
trait TCollectionCoverPhotos
{
    /**
     * @var array<int, FileCoverPhoto>
     */
    private array $coverPhotos;

    public function getCoverPhoto(
        DatabaseObject $object,
        string $coverPhotoIdProperty = 'coverPhotoFileID',
    ): ?FileCoverPhoto {
        $this->loadCoverPhotos($coverPhotoIdProperty);

        $coverPhotoFileID = $object->{$coverPhotoIdProperty};
        if ($coverPhotoFileID === null) {
            return null;
        }

        return $this->coverPhotos[$coverPhotoFileID] ?? null;
    }

    private function loadCoverPhotos(string $coverPhotoIdProperty): void
    {
        if (isset($this->coverPhotos)) {
            return;
        }

        $this->coverPhotos = [];
        $coverPhotoIDs = $this->getCoverPhotoIDs($coverPhotoIdProperty);
        if ($coverPhotoIDs === []) {
            return;
        }

        $fileList = new FileList();
        $fileList->setObjectIDs($coverPhotoIDs);
        $fileList->readObjects();
        $files = $fileList->getObjects();

        $thumbnailList = new FileThumbnailList();
        $thumbnailList->getConditionBuilder()->add("fileID IN (?)", [$fileList->getObjectIDs()]);
        $thumbnailList->readObjects();
        foreach ($thumbnailList as $thumbnail) {
            $files[$thumbnail->fileID]->addThumbnail($thumbnail);
        }

        $this->coverPhotos = \array_map(fn($file) => new FileCoverPhoto($file), $files);
    }

    /**
     * @return int[]
     */
    private function getCoverPhotoIDs(string $coverPhotoIdProperty): array
    {
        \assert($this instanceof DatabaseObjectCollection);

        return \array_map(
            static fn(DatabaseObject $object) => $object->{$coverPhotoIdProperty},
            \array_filter($this->getObjects(), static fn(DatabaseObject $object) => $object->{$coverPhotoIdProperty} !== null)
        );
    }
}
