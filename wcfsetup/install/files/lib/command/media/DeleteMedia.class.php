<?php

namespace wcf\command\media;

use wcf\command\file\DeleteFiles;
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

        $mediaIDs = $files = [];
        foreach ($this->media as $media) {
            $mediaIDs[] = $media->mediaID;

            $file = $media->getFile();
            if ($file !== null) {
                $files[] = $file;
            }
        }

        MediaBuilder::deleteAll($mediaIDs);

        // The files are deleted only after the media files, because deleting a file cannot be rolled back.
        if ($files !== []) {
            new DeleteFiles($files)();
        }
        foreach ($this->media as $media) {
            $media->deleteLegacyFiles();
        }

        EventHandler::getInstance()->fire(new MediaDeleted($this->media));
    }
}
