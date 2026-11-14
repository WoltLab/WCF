<?php

namespace wcf\command\file;

use wcf\data\file\File;
use wcf\data\file\FileBuilder;
use wcf\data\object\type\ObjectType;
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

        if (!$this->copy) {
            return $this->createFile($this->pathname, $objectType);
        }

        // Normalizing the rotation rewrites the file, which must not affect the source of a copy.
        $pathname = FileUtil::getTemporaryFilename();
        if (!\copy($this->pathname, $pathname)) {
            throw new \RuntimeException("The file '{$this->pathname}' could not be copied to '{$pathname}'.");
        }

        try {
            return $this->createFile($pathname, $objectType);
        } finally {
            if (\is_file($pathname)) {
                \unlink($pathname);
            }
        }
    }

    private function createFile(string $pathname, ObjectType $objectType): ?File
    {
        $mimeType = FileUtil::getMimeType($pathname);
        $isImage = ImageUtil::isImageMimeType($mimeType);

        if ($this->exifData === null) {
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
        } else {
            $exifData = $this->exifData;
        }

        $width = $height = null;
        if ($isImage) {
            try {
                $imageData = \getimagesize($pathname);
            } catch (\Throwable) {
                $imageData = false;
            }

            // `getimagesize()` silently returns `false` for some corrupted
            // images, which must not be accepted as an image with unknown
            // dimensions.
            if ($imageData === false) {
                return null;
            }

            [$width, $height] = $imageData;

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
                [$width, $height] = \getimagesize($pathname);
            }
        }

        $file = FileBuilder::forCreate()
            ->setFilename($this->originalFilename)
            ->setFileSize(\filesize($pathname))
            ->setFileHash(\hash_file('sha256', $pathname))
            ->setFileExtension(File::getSafeFileExtension(
                $mimeType,
                $this->originalFilename,
                $objectType->getProcessor(),
            ))
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

        \rename($pathname, $filePath . $file->getSourceFilename());

        return $file;
    }
}
