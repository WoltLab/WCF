<?php

namespace wcf\system\file\processor;

use wcf\data\file\File;

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

    public function canDownload(File $file): bool;

    public function getAllowedFileExtensions(array $context): array;

    public function getTypeName(): string;

    public function getUploadResponse(File $file): array;

    /**
     * @return ThumbnailFormat[]
     */
    public function getThumbnailFormats(): array;
}
