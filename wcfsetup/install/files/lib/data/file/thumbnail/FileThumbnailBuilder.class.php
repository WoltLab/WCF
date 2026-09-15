<?php

namespace wcf\data\file\thumbnail;

use wcf\data\DatabaseObjectBuilder;
use wcf\data\file\File;
use wcf\system\file\processor\ThumbnailFormat;

/**
 * Builder for creating, updating and deleting thumbnails of files.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<FileThumbnail>
 */
final class FileThumbnailBuilder extends DatabaseObjectBuilder
{
    /**
     * Sets the file the thumbnail belongs to.
     */
    public function setFile(File $file): static
    {
        return $this->setFileID($file->fileID);
    }

    /**
     * Sets the id of the file the thumbnail belongs to.
     */
    public function setFileID(int $fileID): static
    {
        $this->properties['fileID'] = $fileID;

        return $this;
    }

    /**
     * Sets the format of the thumbnail, including the checksum that is used to
     * detect thumbnails that need to be regenerated.
     */
    public function setFormat(ThumbnailFormat $format): static
    {
        return $this->setIdentifier($format->identifier)
            ->setFormatChecksum($format->toChecksum());
    }

    /**
     * Sets the identifier of the thumbnail format, empty for the default
     * thumbnail of a file.
     */
    public function setIdentifier(string $identifier): static
    {
        $this->properties['identifier'] = $identifier;

        return $this;
    }

    /**
     * Sets the checksum of the thumbnail format, `null` if the thumbnail does
     * not belong to a format.
     */
    public function setFormatChecksum(?string $formatChecksum): static
    {
        $this->properties['formatChecksum'] = $formatChecksum;

        return $this;
    }

    /**
     * Sets the sha256 hash of the thumbnail.
     */
    public function setFileHash(string $fileHash): static
    {
        $this->properties['fileHash'] = $fileHash;

        return $this;
    }

    /**
     * Sets the file extension that is used to store the thumbnail, must be safe
     * to be served by the webserver.
     */
    public function setFileExtension(string $fileExtension): static
    {
        $this->properties['fileExtension'] = $fileExtension;

        return $this;
    }

    /**
     * Sets the dimensions of the thumbnail.
     */
    public function setDimensions(int $width, int $height): static
    {
        return $this->setWidth($width)->setHeight($height);
    }

    /**
     * Sets the width of the thumbnail.
     */
    public function setWidth(int $width): static
    {
        $this->properties['width'] = $width;

        return $this;
    }

    /**
     * Sets the height of the thumbnail.
     */
    public function setHeight(int $height): static
    {
        $this->properties['height'] = $height;

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['fileID', 'identifier', 'fileHash', 'fileExtension', 'width', 'height'];
    }

    #[\Override]
    protected static function beforeDeleteAll(array $objectIDs): void
    {
        $thumbnailList = new FileThumbnailList();
        $thumbnailList->getConditionBuilder()->add('thumbnailID IN (?)', [$objectIDs]);
        $thumbnailList->readObjects();

        foreach ($thumbnailList as $thumbnail) {
            @\unlink($thumbnail->getPathname());
        }
    }
}
