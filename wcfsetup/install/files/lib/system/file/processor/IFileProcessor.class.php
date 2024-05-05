<?php

namespace wcf\system\file\processor;

use wcf\data\file\File;
use wcf\data\file\thumbnail\FileThumbnail;

/**
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
interface IFileProcessor
{
    public function acceptUpload(string $filename, int $fileSize, array $context): FileProcessorPreflightResult;

    public function adopt(File $file, array $context): void;

    public function adoptThumbnail(FileThumbnail $thumbnail): void;

    public function canDelete(File $file): bool;

    public function canDownload(File $file): bool;

    /**
     * @param list<int> $files
     * @param list<int> $thumbnails
     */
    public function delete(array $fileIDs, array $thumbnailIDs): void;

    public function getAllowedFileExtensions(array $context): array;

    public function getResizeConfiguration(): ResizeConfiguration;

    public function getObjectTypeName(): string;

    /**
     * @return ThumbnailFormat[]
     */
    public function getThumbnailFormats(): array;

    public function getUploadResponse(File $file): array;
}
