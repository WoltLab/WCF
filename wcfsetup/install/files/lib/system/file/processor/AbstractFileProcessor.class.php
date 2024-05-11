<?php

namespace wcf\system\file\processor;

use wcf\data\file\File;
use wcf\data\file\thumbnail\FileThumbnail;

/**
 * Default implementation for file processors that provide meaningful defaults
 * for most types of file uploads.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
abstract class AbstractFileProcessor implements IFileProcessor
{
    #[\Override]
    public function adoptThumbnail(FileThumbnail $thumbnail): void
    {
        // There are no thumbnails in the default implementation.
    }

    #[\Override]
    public function getAllowedFileExtensions(array $context): array
    {
        // Allow all file types to be uploaded.
        return ['*'];
    }

    #[\Override]
    public function getFileCacheDuration(File $file): FileCacheDuration
    {
        return FileCacheDuration::oneYear();
    }

    #[\Override]
    public function getResizeConfiguration(): ResizeConfiguration
    {
        // Disable client-side resizing.
        return ResizeConfiguration::unbounded();
    }

    #[\Override]
    public function getThumbnailFormats(): array
    {
        // Do not generate any thumbnails.
        return [];
    }

    #[\Override]
    public function getUploadResponse(File $file): array
    {
        // There is usually no need for meta data to be sent to the client.
        return [];
    }

    #[\Override]
    public function trackDownload(File $file): void
    {
        // Do not track downloads.
    }
}
