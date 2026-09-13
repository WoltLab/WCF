<?php

namespace wcf\command\file;

use wcf\data\file\File;
use wcf\data\file\FileBuilder;
use wcf\data\file\temporary\FileTemporary;
use wcf\event\file\UploadCompleted;
use wcf\system\event\EventHandler;
use wcf\util\ExifUtil;
use wcf\util\FileUtil;
use wcf\util\ImageUtil;

/**
 * Promotes a completely uploaded temporary file into a regular file.
 *
 * @author      Alexander Ebert, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class CreateFileFromTemporary
{
    public function __construct(
        private readonly FileTemporary $fileTemporary,
    ) {}

    public function __invoke(): File
    {
        $pathname = $this->fileTemporary->getPathname();
        $mimeType = FileUtil::getMimeType($pathname);
        $isImage = ImageUtil::isImageMimeType($mimeType);

        $width = $height = null;
        if ($isImage) {
            [$width, $height] = \getimagesize($pathname);
        }

        $exifData = $this->fileTemporary->exifData;
        if ($exifData !== null) {
            $exifData = \unserialize($exifData);
        }

        $fileSize = $this->fileTemporary->fileSize;
        $fileHash = $this->fileTemporary->fileHash;
        if ($isImage) {
            $imageWasModified = false;
            try {
                $imageWasModified = ExifUtil::normalizeImageRotation(
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

        $file = FileBuilder::forCreate()
            ->setFilename($this->fileTemporary->filename)
            ->setFileSize($fileSize)
            ->setFileHash($fileHash)
            ->setFileExtension(File::getSafeFileExtension($mimeType, $this->fileTemporary->filename))
            ->setObjectTypeID($this->fileTemporary->objectTypeID)
            ->setMimeType($mimeType)
            ->setDimensions($width, $height)
            ->setUploadTime(\TIME_NOW)
            ->setExifData($exifData)
            ->create();

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
}
