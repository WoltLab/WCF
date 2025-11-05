<?php

namespace wcf\data\file;

use wcf\data\DatabaseObjectEditor;
use wcf\data\file\temporary\FileTemporary;
use wcf\data\file\thumbnail\FileThumbnailEditor;
use wcf\event\file\UploadCompleted;
use wcf\system\event\EventHandler;
use wcf\system\file\processor\FileProcessor;
use wcf\system\image\ImageHandler;
use wcf\util\ExifUtil;
use wcf\util\FileUtil;
use wcf\util\JSON;

/**
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 *
 * @mixin File
 * @extends DatabaseObjectEditor<File>
 */
class FileEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = File::class;

    public function deleteFiles(): void
    {
        @\unlink($this->getPathname());

        $thumbnailIDs = \array_column($this->getThumbnails(), 'thumbnailID');
        if ($thumbnailIDs !== []) {
            FileThumbnailEditor::deleteAll($thumbnailIDs);
        }
    }

    public static function deleteAll(array $objectIDs = [])
    {
        $fileList = new FileList();
        $fileList->loadThumbnails = true;
        $fileList->getConditionBuilder()->add("fileID IN (?)", [$objectIDs]);
        $fileList->readObjects();
        $files = $fileList->getObjects();
        if (\count($files) === 0) {
            return 0;
        }

        foreach ($files as $file) {
            (new FileEditor($file))->deleteFiles();
        }

        return parent::deleteAll($objectIDs);
    }

    public static function createFromTemporary(FileTemporary $fileTemporary): File
    {
        $pathname = $fileTemporary->getPathname();
        $mimeType = FileUtil::getMimeType($pathname);
        $isImage = match ($mimeType) {
            'image/gif' => true,
            'image/jpeg' => true,
            'image/png' => true,
            'image/webp' => true,
            default => false,
        };

        $width = $height = null;
        if ($isImage) {
            [$width, $height] = \getimagesize($pathname);
        }

        $exifData = $fileTemporary->exifData;
        if ($exifData !== null) {
            $exifData = JSON::decode($exifData);
        }

        $fileSize = $fileTemporary->fileSize;
        $fileHash = $fileTemporary->fileHash;
        if ($isImage) {
            $imageWasModified = false;
            try {
                $imageWasModified = self::normalizeImageRotation(
                    $pathname,
                    $width,
                    $height,
                    $mimeType,
                    $exifData,
                );

                if ($imageWasModified && $exifData !== null) {
                    unset($exifData['IFD0']['Orientation']);
                }
            } catch (\Throwable) {
            }

            if ($imageWasModified) {
                $fileSize = \filesize($pathname);
                $fileHash = \hash_file('sha256', $pathname);
                [$width, $height] = \getimagesize($pathname);
            }
        }

        $fileAction = new FileAction([], 'create', ['data' => [
            'filename' => $fileTemporary->filename,
            'fileSize' => $fileSize,
            'fileHash' => $fileHash,
            'fileExtension' => File::getSafeFileExtension($mimeType, $fileTemporary->filename),
            'objectTypeID' => $fileTemporary->objectTypeID,
            'mimeType' => $mimeType,
            'width' => $width,
            'height' => $height,
            'uploadTime' => \TIME_NOW,
            'exifData' => $exifData !== null ? JSON::encode($exifData) : null,
        ]]);
        $file = $fileAction->executeAction()['returnValues'];
        \assert($file instanceof File);

        $filePath = $file->getPath();
        if (!\is_dir($filePath)) {
            \mkdir($filePath, recursive: true);
        }

        \rename(
            $pathname,
            $filePath . $file->getSourceFilename()
        );

        $event = new UploadCompleted($file);
        EventHandler::getInstance()->fire($event);

        return $event->getFile();
    }

    /**
     * @param null|array<string, array<string, mixed>> $exifData
     */
    public static function createFromExistingFile(
        string $pathname,
        string $originalFilename,
        string $objectTypeName,
        bool $copy = false,
        ?int $uploadTime = null,
        ?array $exifData = null,
    ): ?File {
        if (!\is_readable($pathname)) {
            return null;
        }

        $objectType = FileProcessor::getInstance()->getObjectType($objectTypeName);
        if ($objectType === null) {
            throw new \RuntimeException("The object type '{$objectTypeName}' is not valid.");
        }

        $mimeType = FileUtil::getMimeType($pathname);
        $isImage = match ($mimeType) {
            'image/gif' => true,
            'image/jpeg' => true,
            'image/png' => true,
            'image/webp' => true,
            default => false,
        };

        if ($exifData === null) {
            $exifData = ExifUtil::getExifData($pathname);

            // Remove the `FILE` and `COMPUTED` section because those contain
            // garbled data anyway and we do not need them in the first place.
            unset($exifData['FILE'], $exifData['COMPUTED']);

            // We can also discard the `THUMBNAIL` section because it is a
            // pointless feature and we’re not extracting it either.
            unset($exifData['THUMBNAIL']);

            if ($exifData === []) {
                $exifData = null;
            }
        }

        $width = $height = null;
        if ($isImage) {
            try {
                [$width, $height] = \getimagesize($pathname);
            } catch (\Throwable) {
                return null;
            }

            $imageWasModified = false;
            try {
                $imageWasModified = self::normalizeImageRotation(
                    $pathname,
                    $width,
                    $height,
                    $mimeType,
                    $exifData,
                );

                if ($imageWasModified && $exifData !== null) {
                    unset($exifData['IFD0']['Orientation']);
                }
            } catch (\Throwable) {
            }

            if ($imageWasModified) {
                [$width, $height] = \getimagesize($pathname);
            }
        }

        $fileAction = new FileAction([], 'create', ['data' => [
            'filename' => $originalFilename,
            'fileSize' => \filesize($pathname),
            'fileHash' => \hash_file('sha256', $pathname),
            'fileExtension' => File::getSafeFileExtension($mimeType, $originalFilename),
            'objectTypeID' => $objectType->objectTypeID,
            'mimeType' => $mimeType,
            'width' => $width,
            'height' => $height,
            'uploadTime' => $uploadTime,
            'exifData' => $exifData !== null ? JSON::encode($exifData) : null,
        ]]);
        $file = $fileAction->executeAction()['returnValues'];
        \assert($file instanceof File);

        $filePath = $file->getPath();
        if (!\is_dir($filePath)) {
            \mkdir($filePath, recursive: true);
        }

        if ($copy) {
            \copy($pathname, $filePath . $file->getSourceFilename());
        } else {
            \rename($pathname, $filePath . $file->getSourceFilename());
        }

        return $file;
    }

    /**
     * Normalizes the image rotation by rotating images that been taken while
     * the camera was tilted or upside down.
     *
     * Rotating the image can cause the dimensions to change, the image size to
     * differ and the file hash to be different.
     *
     * @param null|array<string, array<string, mixed>> $exifData
     * @return bool true if the image was modified.
     */
    private static function normalizeImageRotation(
        string $pathname,
        int $width,
        int $height,
        string $mimeType,
        ?array $exifData,
    ): bool {
        $adapter = ImageHandler::getInstance()->getAdapter();
        if (!$adapter->checkMemoryLimit($width, $height, $mimeType)) {
            return false;
        }

        $exifData ??= ExifUtil::getExifData($pathname);
        if ($exifData === []) {
            return false;
        }

        $orientation = ExifUtil::getOrientation($exifData);
        if ($orientation === ExifUtil::ORIENTATION_ORIGINAL) {
            return false;
        }

        $rotateByDegrees = match ($orientation) {
            ExifUtil::ORIENTATION_180_ROTATE => 180,
            ExifUtil::ORIENTATION_90_ROTATE => 90,
            ExifUtil::ORIENTATION_270_ROTATE => 270,
            // Any other rotation is unsupported.
            default => null,
        };

        if ($rotateByDegrees === null) {
            return false;
        }

        $adapter->loadFile($pathname);

        $image = $adapter->rotate($rotateByDegrees);
        if ($image instanceof \Imagick) {
            $image->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);
        }

        $adapter->load($image, $adapter->getType());

        $adapter->writeImage($pathname);

        return true;
    }
}
