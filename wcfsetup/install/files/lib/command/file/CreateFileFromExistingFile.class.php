<?php

namespace wcf\command\file;

use wcf\data\file\File;
use wcf\data\file\FileBuilder;
use wcf\system\file\processor\FileProcessor;
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
final class CreateFileFromExistingFile
{
    /**
     * @param null|array<string, array<string, mixed>> $exifData
     */
    public function __construct(
        private readonly string $pathname,
        private readonly string $originalFilename,
        private readonly string $objectTypeName,
        private readonly bool $copy = false,
        private readonly ?int $uploadTime = null,
        private readonly ?array $exifData = null,
    ) {}

    public function __invoke(): ?File
    {
        if (!\is_readable($this->pathname)) {
            return null;
        }

        $objectType = FileProcessor::getInstance()->getObjectType($this->objectTypeName);
        if ($objectType === null) {
            throw new \RuntimeException("The object type '{$this->objectTypeName}' is not valid.");
        }

        $mimeType = FileUtil::getMimeType($this->pathname);
        $isImage = ImageUtil::isImageMimeType($mimeType);

        if ($this->exifData === null) {
            $exifData = ExifUtil::getExifData($this->pathname);

            // Remove the `FILE` and `COMPUTED` section because those contain
            // garbled data anyway and we do not need them in the first place.
            unset($exifData['FILE'], $exifData['COMPUTED']);

            // We can also discard the `THUMBNAIL` section because it is a
            // pointless feature and we’re not extracting it either.
            unset($exifData['THUMBNAIL']);

            if ($exifData === []) {
                $exifData = null;
            }
        } else {
            $exifData = $this->exifData;
        }

        $width = $height = null;
        if ($isImage) {
            try {
                [$width, $height] = \getimagesize($this->pathname);
            } catch (\Throwable) {
                return null;
            }

            $imageWasModified = false;
            try {
                $imageWasModified = ExifUtil::normalizeImageRotation(
                    $this->pathname,
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
                [$width, $height] = \getimagesize($this->pathname);
            }
        }

        $file = FileBuilder::forCreate()
            ->setFilename($this->originalFilename)
            ->setFileSize(\filesize($this->pathname))
            ->setFileHash(\hash_file('sha256', $this->pathname))
            ->setFileExtension(File::getSafeFileExtension($mimeType, $this->originalFilename))
            ->setObjectType($objectType)
            ->setMimeType($mimeType)
            ->setDimensions($width, $height)
            ->setUploadTime($this->uploadTime)
            ->setExifData($exifData)
            ->create();

        $filePath = $file->getPath();
        if (!\is_dir($filePath)) {
            \mkdir($filePath, recursive: true);
        }

        if ($this->copy) {
            \copy($this->pathname, $filePath . $file->getSourceFilename());
        } else {
            \rename($this->pathname, $filePath . $file->getSourceFilename());
        }

        return $file;
    }
}
