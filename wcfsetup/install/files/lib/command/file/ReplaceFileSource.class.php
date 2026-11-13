<?php

namespace wcf\command\file;

use wcf\data\file\File;
use wcf\data\file\FileBuilder;
use wcf\data\file\thumbnail\FileThumbnailBuilder;
use wcf\system\file\processor\exception\DamagedImage;
use wcf\system\file\processor\FileProcessor;
use wcf\system\WCF;
use wcf\util\FileUtil;

use function wcf\functions\exception\logThrowable;

/**
 * Replaces the physical file of an entry with a new file.
 *
 * A new source with a different file hash invalidates the files derived from
 * the old one: the WebP variant and all thumbnails are deleted. Callers that
 * only strip metadata or convert the format pass `$visuallyIdentical = true`
 * to keep the derived files.
 *
 * If `$regenerateThumbnails` is true and the file had thumbnails or its WebP
 * variant was deleted, the derived files are generated again immediately.
 * Otherwise this is left to the caller, e.g. by running the same steps as the
 * `GenerateThumbnails` endpoint.
 *
 * The processor is not notified, the caller must invoke
 * `IFileProcessor::sourceFilenameChanged()` when the file hash has changed.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class ReplaceFileSource
{
    public function __construct(
        private readonly File $file,
        private readonly string $pathname,
        private readonly ?string $filename = null,
        private readonly ?bool $regenerateThumbnails = true,
        private readonly bool $visuallyIdentical = false,
    ) {}

    public function __invoke(): File
    {
        $this->validatePathname($this->pathname);
        $this->validatePathname($this->file->getPathname());

        // A missing variant is not fatal, `replaceSource()` clears the reference.
        $pathnameWebp = $this->file->getPathnameWebp();
        if ($pathnameWebp !== null && \file_exists($pathnameWebp)) {
            $this->validatePathname($pathnameWebp);
        }

        $file = $this->replaceSource();
        $file = $this->discardWebpVariantOfWebpFile($file);

        $thumbnailIDs = [];
        if ($this->regenerateThumbnails === true || $this->invalidatesDerivedFiles($file)) {
            $thumbnailIDs = $this->getThumbnailIDs($file);
        }

        $hadDerivedFiles = $thumbnailIDs !== [];
        if ($this->invalidatesDerivedFiles($file)) {
            if ($thumbnailIDs !== []) {
                FileThumbnailBuilder::deleteAll($thumbnailIDs);
            }

            $hadDerivedFiles = $hadDerivedFiles || $this->file->fileHashWebp !== null;
        }

        if ($this->regenerateThumbnails === true && $hadDerivedFiles) {
            $file = $this->regenerateDerivedFiles($file);
        }

        return $file;
    }

    private function invalidatesDerivedFiles(File $updatedFile): bool
    {
        return !$this->visuallyIdentical && $updatedFile->fileHash !== $this->file->fileHash;
    }

    private function validatePathname(?string $pathname): void
    {
        if ($pathname === null) {
            return;
        }

        if (!\file_exists($pathname)) {
            throw new \RuntimeException("The file '{$pathname}' does not exist.");
        }

        if (!\is_writable($pathname)) {
            throw new \RuntimeException("The file '{$pathname}' is not writable.");
        }
    }

    private function replaceSource(): File
    {
        $mimeType = FileUtil::getMimeType($this->pathname);
        $isImage = match ($mimeType) {
            'image/gif' => true,
            'image/jpeg' => true,
            'image/png' => true,
            'image/webp' => true,
            default => false,
        };

        $width = $height = null;
        if ($isImage) {
            [$width, $height] = \getimagesize($this->pathname);
        }

        $filename = ($this->filename !== null && $this->filename !== '')
            ? $this->filename
            : \basename($this->pathname);
        $fileSize = \filesize($this->pathname);
        $fileHash = \hash_file('sha256', $this->pathname);
        $fileExtension = File::getSafeFileExtension($mimeType, $filename, $this->file->getProcessor());

        // The following code uses a transaction and a write lock on the file in
        // order to guarantee this operation to atomic. The replacement is most
        // likely the result of a post-processing step of the file thus is a
        // repeatable action.
        //
        // If the rename fails for whatever reasons, then this file becomes
        // corrupted without being able to recover the old state. This guard
        // code prevents this from happening.
        WCF::getDB()->beginTransaction();
        $committed = false;
        try {
            $sql = "SELECT      *
                    FROM        wcf1_file
                    WHERE       fileID = ?
                    FOR UPDATE";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$this->file->fileID]);

            $updatedFile = FileBuilder::forUpdate($this->file)
                ->setFilename($filename)
                ->setFileSize($fileSize)
                ->setFileHash($fileHash)
                ->setFileExtension($fileExtension)
                ->setMimeType($mimeType)
                ->setDimensions($width, $height)
                ->update();

            $path = \dirname($updatedFile->getPathname());
            FileUtil::makePath($path);

            \rename(
                $this->pathname,
                $updatedFile->getPathname(),
            );

            // Remove the previous file source because the new filename could
            // differ due to the checksums.
            if ($this->file->getPathname() !== $updatedFile->getPathname()) {
                \unlink($this->file->getPathname());
            }

            // Move the WebP variant to the new location, unless it no longer
            // matches the new source.
            $webpVariant = $this->file->getPathnameWebp();
            if ($webpVariant !== null) {
                if ($updatedFile->mimeType === 'image/webp' || $this->invalidatesDerivedFiles($updatedFile)) {
                    // The file might be missing if it was used to replace the
                    // original version with it or vanished from disk.
                    @\unlink($webpVariant);

                    $updatedFile = FileBuilder::forUpdate($updatedFile)
                        ->setFileHashWebp(null)
                        ->update();
                } else {
                    if (\file_exists($webpVariant)) {
                        $newWebpVariant = $updatedFile->getPathnameWebp();
                        \assert($newWebpVariant !== null);

                        \rename(
                            $webpVariant,
                            $newWebpVariant,
                        );
                    } else {
                        // The variant file is missing on disk, clear the
                        // stale reference.
                        $updatedFile = FileBuilder::forUpdate($updatedFile)
                            ->setFileHashWebp(null)
                            ->update();
                    }
                }
            }

            WCF::getDB()->commitTransaction();
            $committed = true;

            return $updatedFile;
        } finally {
            if (!$committed) {
                WCF::getDB()->rollBackTransaction();
            }
        }
    }

    private function discardWebpVariantOfWebpFile(File $file): File
    {
        if ($file->mimeType !== 'image/webp') {
            return $file;
        }

        $pathname = $file->getPathnameWebp();
        if ($pathname === null) {
            return $file;
        }

        if (\file_exists($pathname)) {
            \unlink($file->getPathnameWebp());
        }

        return FileBuilder::forUpdate($file)
            ->setFileHashWebp(null)
            ->update();
    }

    /**
     * @return list<int>
     */
    private function getThumbnailIDs(File $file): array
    {
        $sql = "SELECT  thumbnailID
                FROM    wcf1_file_thumbnail
                WHERE   fileID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$file->fileID]);

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    private function regenerateDerivedFiles(File $file): File
    {
        try {
            $file = FileProcessor::getInstance()->stripExif($file);
            $file = FileProcessor::getInstance()->generateWebpVariant($file);
            $file = FileProcessor::getInstance()->convertImageFormat($file);
            FileProcessor::getInstance()->generateThumbnails($file);
        } catch (DamagedImage $e) {
            logThrowable($e);
        } finally {
            return $file;
        }
    }
}
