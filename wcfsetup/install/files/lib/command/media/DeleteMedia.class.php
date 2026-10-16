<?php

namespace wcf\command\media;

use wcf\data\media\Media;
use wcf\data\media\MediaBuilder;
use wcf\event\media\MediaDeleted;
use wcf\system\event\EventHandler;

/**
 * Deletes media files, including their physical files.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class DeleteMedia
{
    /**
     * @param Media[] $media
     */
    public function __construct(
        private readonly array $media,
    ) {}

    public function __invoke(): void
    {
        if ($this->media === []) {
            return;
        }

        $mediaIDs = \array_values(\array_map(static fn(Media $media) => $media->mediaID, $this->media));

        MediaBuilder::deleteAll($mediaIDs);

        // The files are deleted only after the media files, because deleting a file cannot be rolled back.
        foreach ($this->media as $media) {
            $this->deleteLegacyFiles($media);
        }

        EventHandler::getInstance()->fire(new MediaDeleted($this->media));
    }

    private function deleteLegacyFiles(Media $media): void
    {
        @\unlink($media->getLocation());

        if ($media->isImage !== 0) {
            foreach (\array_keys(Media::getThumbnailSizes()) as $size) {
                @\unlink($media->getThumbnailLocation($size));
            }
        }
    }
}
