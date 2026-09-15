<?php

namespace wcf\data\file;

use wcf\data\DatabaseObjectBuilder;
use wcf\data\file\thumbnail\FileThumbnailBuilder;
use wcf\data\object\type\ObjectType;

/**
 * Builder for creating, updating and deleting files.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<File>
 */
final class FileBuilder extends DatabaseObjectBuilder
{
    /**
     * Sets the original name of the file as provided by the uploader.
     */
    public function setFilename(string $filename): static
    {
        $this->properties['filename'] = $filename;

        return $this;
    }

    /**
     * Sets the size of the file in bytes.
     */
    public function setFileSize(int $fileSize): static
    {
        $this->properties['fileSize'] = $fileSize;

        return $this;
    }

    /**
     * Sets the sha256 hash of the file.
     */
    public function setFileHash(string $fileHash): static
    {
        $this->properties['fileHash'] = $fileHash;

        return $this;
    }

    /**
     * Sets the file extension that is used to store the file, must be safe to
     * be served by the webserver. See `File::getSafeFileExtension()`.
     */
    public function setFileExtension(string $fileExtension): static
    {
        $this->properties['fileExtension'] = $fileExtension;

        return $this;
    }

    /**
     * Sets the object type of the file, must be an object type of the
     * definition `com.woltlab.wcf.file`.
     */
    public function setObjectType(ObjectType $objectType): static
    {
        return $this->setObjectTypeID($objectType->objectTypeID);
    }

    /**
     * Sets the id of the object type of the file.
     */
    public function setObjectTypeID(?int $objectTypeID): static
    {
        $this->properties['objectTypeID'] = $objectTypeID;

        return $this;
    }

    /**
     * Sets the mime type of the file.
     */
    public function setMimeType(string $mimeType): static
    {
        $this->properties['mimeType'] = $mimeType;

        return $this;
    }

    /**
     * Sets the dimensions of the file, images only.
     */
    public function setDimensions(?int $width, ?int $height): static
    {
        return $this->setWidth($width)->setHeight($height);
    }

    /**
     * Sets the width of the file, images only.
     */
    public function setWidth(?int $width): static
    {
        $this->properties['width'] = $width;

        return $this;
    }

    /**
     * Sets the height of the file, images only.
     */
    public function setHeight(?int $height): static
    {
        $this->properties['height'] = $height;

        return $this;
    }

    /**
     * Sets the sha256 hash of the WebP variant of the file, `null` if there is
     * no such variant.
     */
    public function setFileHashWebp(?string $fileHashWebp): static
    {
        $this->properties['fileHashWebp'] = $fileHashWebp;

        return $this;
    }

    /**
     * Sets the timestamp at which the file has been uploaded.
     */
    public function setUploadTime(?int $uploadTime): static
    {
        $this->properties['uploadTime'] = $uploadTime;

        return $this;
    }

    /**
     * Sets the exif data that has been extracted from the file.
     *
     * @param null|array<string, array<string, mixed>> $exifData
     */
    public function setExifData(?array $exifData): static
    {
        $this->properties['exifData'] = $exifData !== null ? \serialize($exifData) : null;

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['filename', 'fileSize', 'fileHash', 'fileExtension', 'mimeType'];
    }

    #[\Override]
    protected static function beforeDeleteAll(array $objectIDs): void
    {
        $fileList = new FileList();
        $fileList->loadThumbnails = true;
        $fileList->getConditionBuilder()->add('fileID IN (?)', [$objectIDs]);
        $fileList->readObjects();

        $thumbnailIDs = [];
        foreach ($fileList as $file) {
            @\unlink($file->getPathname());

            $pathnameWebp = $file->getPathnameWebp();
            if ($pathnameWebp !== null) {
                @\unlink($pathnameWebp);
            }

            $thumbnailIDs = [
                ...$thumbnailIDs,
                ...\array_column($file->getThumbnails(), 'thumbnailID'),
            ];
        }

        if ($thumbnailIDs !== []) {
            FileThumbnailBuilder::deleteAll($thumbnailIDs);
        }
    }
}
