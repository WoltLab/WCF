<?php

namespace wcf\command\file;

use wcf\data\file\File;
use wcf\data\file\FileEditor;
use wcf\system\file\processor\exception\DamagedImage;
use wcf\system\file\processor\FileProcessor;
use wcf\system\WCF;
use wcf\util\FileUtil;

use wcf\http\error\ExceptionLogger;

/**
 * Replaces the physical file of an entry with a new file. If there are any
 * existing thumbnails, those will be regenerated immediately.
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
    ) {}

    public function __invoke(): File
    {
        $this->validatePathname($this->pathname);
        $this->validatePathname($this->file->getPathname());
        $this->validatePathname($this->file->getPathnameWebp());

        $file = $this->replaceSource();
        $file = $this->discardWebpVariantOfWebpFile($file);

        if ($this->regenerateThumbnails) {
            $file = $this->regenerateExistingThumbnails($file);
        }

        return $file;
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

        $filename = $this->filename ? $this->filename : \basename($this->pathname);
        $fileSize = \filesize($this->pathname);
        $fileHash = \hash_file('sha256', $this->pathname);
        $fileExtension = File::getSafeFileExtension($mimeType, $filename);

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

            (new FileEditor($this->file))->update([
                'filename' => $filename,
                'fileSize' => $fileSize,
                'fileHash' => $fileHash,
                'fileExtension' => $fileExtension,
                'mimeType' => $mimeType,
                'width' => $width,
                'height' => $height,
            ]);

            $updatedFile = new File($this->file->fileID);

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

            // Move the WebP thumbnail to the new location.
            $webpVariant = $this->file->getPathnameWebp();
            if ($webpVariant !== null) {
                if ($updatedFile->mimeType === 'image/webp') {
                    // The file might be missing if it was used to replace the
                    // original version with it.
                    @\unlink($webpVariant);

                    (new FileEditor($updatedFile))->update([
                        'fileHashWebp' => null,
                    ]);
                    $updatedFile = new File($updatedFile->fileID);
                } else {
                    $newWebpVariant = $updatedFile->getPathnameWebp();
                    \assert($newWebpVariant !== null);

                    \rename(
                        $webpVariant,
                        $newWebpVariant,
                    );
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

        (new FileEditor($file))->update([
            'fileHashWebp' => null,
        ]);

        return new File($file->fileID);
    }

    private function regenerateExistingThumbnails(File $file): File
    {
        $sql = "SELECT  COUNT(*)
                FROM    wcf1_file_thumbnail
                WHERE   fileID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$file->fileID]);
        $count = $statement->fetchSingleColumn();
        if ($count === 0) {
            return $file;
        }

        try {
            $file = FileProcessor::getInstance()->generateWebpVariant($file);
            $file = FileProcessor::getInstance()->stripExif($file);
            $file = FileProcessor::getInstance()->convertImageFormat($file);
            FileProcessor::getInstance()->generateThumbnails($file);
        } catch (DamagedImage $e) {
            ExceptionLogger::log($e);
        } finally {
            return $file;
        }
    }
}
