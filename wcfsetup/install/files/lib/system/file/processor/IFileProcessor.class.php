<?php

namespace wcf\system\file\processor;

use wcf\data\file\File;
use wcf\data\file\thumbnail\FileThumbnail;
use wcf\system\exception\UserInputException;

/**
 * File processors are responsible to validate and process any file uploads
 * made by the client. They are also queried for permission checks.
 *
 * It is strongly recommended to extend `AbstractFileProcessor` instead of
 * implementing this interface directly.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
interface IFileProcessor
{
    /**
     * Validates if this file may be uploaded.
     *
     * The `$context` variable is echoed from the `<woltlab-core-file-upload>`
     * element and intended to provide additional context to make decisions. The
     * value is stored for use with the temporary file later.
     */
    public function acceptUpload(string $filename, int $fileSize, array $context): FileProcessorPreflightResult;

    /**
     * Validates the uploaded file.
     *
     * @throws UserInputException if the file is invalid.
     */
    public function validateUpload(File $file): void;

    /**
     * Checks if the given `$file` can be assigned to the object referenced by the information
     * contained in `$context`.
     *
     * The `$file` can be assigned if one of the following conditions is met:
     * - The file has not yet been assigned to any object
     * - The file is already assigned to the object referenced by `$context`
     */
    public function canAdopt(File $file, array $context): bool;

    /**
     * Notifies the file processor that the upload of a file has been completed
     * that belongs to this type.
     *
     * `$context` are the exact same values that have previously been passed to
     * `canAdopt()` before.
     */
    public function adopt(File $file, array $context): void;

    /**
     * Notifies the file processor that a thumbnail for one of its files has
     * been generated.
     */
    public function adoptThumbnail(FileThumbnail $thumbnail): void;

    /**
     * Validates that the current user can delete this file.
     */
    public function canDelete(File $file): bool;

    /**
     * Validates that the current user may download this file.
     *
     * This method is only invoked for files that are piped through PHP, some
     * static files that are deemed safe are served through the web server,
     * bypassing PHP and this permission check entirely.
     */
    public function canDownload(File $file): bool;

    /**
     * Counts the numbers of existing files for the provided context.
     *
     * The purpose of this method is to enforce upload limits for UGC, but an
     * implementation may opt out of this by returning `null` which means that
     * it does not track this for whatever reason.
     *
     * @param array<string,string> $context
     * @return null|int Number of existing files or `null` if this should not be enforced
     */
    public function countExistingFiles(array $context): ?int;

    /**
     * Notifies the file processor that the list of provided file and thumbnail
     * ids have been deleted.
     *
     * @param list<int> $files
     * @param list<int> $thumbnails
     */
    public function delete(array $fileIDs, array $thumbnailIDs): void;

    /**
     * Returns the list of file extensions that are permitted for the upload.
     *
     * The special value '*' indicates that all file extensions are acceptable.
     *
     * @return list<string>
     */
    public function getAllowedFileExtensions(array $context): array;

    /**
     * Limits how long a file may be cached by the browser. Should use a low
     * value for files that are not persisted yet.
     */
    public function getFileCacheDuration(File $file): FileCacheDuration;

    /**
     * Limits the maximum number of files that can be uploaded for the provided
     * context.
     *
     * @return null|int Maximum number of files or `null` for an indefinite amount.
     */
    public function getMaximumCount(array $context): ?int;

    /**
     * Limits the maximum size of an uploade file.
     *
     * @param array<string,string> $context
     * @return null|int Maximum size in bytes or null to disable the limit.
     */
    public function getMaximumSize(array $context): ?int;

    /**
     * Controls the client-side resizing of some types of images before they are
     * being uploaded to the server.
     */
    public function getResizeConfiguration(): ResizeConfiguration;

    /**
     * Returns the name of the object type of this file processor.
     */
    public function getObjectTypeName(): string;

    /**
     * Returns the list of thumbnails that should be generated for images.
     *
     * @return list<ThumbnailFormat>
     */
    public function getThumbnailFormats(): array;

    /**
     * Returns additional meta data for this file that will be transmitted to
     * the client.
     */
    public function getUploadResponse(File $file): array;

    /**
     * Invoked whenever a file is being downloaded. This does not work for some
     * file types that are served by the web server itself.
     */
    public function trackDownload(File $file): void;
}
