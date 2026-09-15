<?php

namespace wcf\data\file\temporary;

use wcf\data\DatabaseObjectBuilder;
use wcf\data\object\type\ObjectType;

/**
 * Builder for creating, updating and deleting temporary files.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<FileTemporary>
 */
final class FileTemporaryBuilder extends DatabaseObjectBuilder
{
    /**
     * Sets the identifier of the temporary file that is used to reference it
     * while the upload is in progress.
     */
    public function setIdentifier(string $identifier): static
    {
        return $this->setID($identifier);
    }

    /**
     * Sets the timestamp at which the upload has been announced.
     */
    public function setTime(int $time): static
    {
        $this->properties['time'] = $time;

        return $this;
    }

    /**
     * Sets the original name of the file as provided by the uploader.
     */
    public function setFilename(string $filename): static
    {
        $this->properties['filename'] = $filename;

        return $this;
    }

    /**
     * Sets the expected size of the file in bytes.
     */
    public function setFileSize(int $fileSize): static
    {
        $this->properties['fileSize'] = $fileSize;

        return $this;
    }

    /**
     * Sets the expected sha256 hash of the file.
     */
    public function setFileHash(string $fileHash): static
    {
        $this->properties['fileHash'] = $fileHash;

        return $this;
    }

    /**
     * Sets the object type of the file, must be an object type of the
     * definition `com.woltlab.wcf.file`.
     */
    public function setObjectType(?ObjectType $objectType): static
    {
        return $this->setObjectTypeID($objectType?->objectTypeID);
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
     * Sets the context of the upload as provided by the uploader, must be a
     * JSON encoded string.
     */
    public function setContext(?string $context): static
    {
        $this->properties['context'] = $context;

        return $this;
    }

    /**
     * Sets the number of chunks the file is split into, marking all of them as
     * pending.
     *
     * @throws \InvalidArgumentException if the number of chunks is less than 1
     */
    public function setNumberOfChunks(int $numberOfChunks): static
    {
        if ($numberOfChunks < 1) {
            throw new \InvalidArgumentException("The number of chunks must be at least 1, '{$numberOfChunks}' given.");
        }

        $this->properties['chunks'] = \str_repeat('0', $numberOfChunks);

        return $this;
    }

    /**
     * Marks the chunk identified by its sequence number as written.
     *
     * @throws \OutOfRangeException if there is no chunk with the given sequence number
     */
    public function markChunkAsWritten(int $sequenceNo): static
    {
        $chunks = $this->properties['chunks'] ?? $this->getObject()->chunks;
        if ($sequenceNo < 0 || $sequenceNo >= \strlen($chunks)) {
            throw new \OutOfRangeException(
                \sprintf(
                    "Cannot access chunk #%d of %d",
                    $sequenceNo,
                    \strlen($chunks),
                ),
            );
        }

        $chunks[$sequenceNo] = '1';
        $this->properties['chunks'] = $chunks;

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
        return ['identifier', 'time', 'filename', 'fileSize', 'fileHash', 'chunks'];
    }

    #[\Override]
    protected static function beforeDeleteAll(array $objectIDs): void
    {
        $fileTemporaryList = new FileTemporaryList();
        $fileTemporaryList->getConditionBuilder()->add('identifier IN (?)', [$objectIDs]);
        $fileTemporaryList->readObjects();

        foreach ($fileTemporaryList as $fileTemporary) {
            @\unlink($fileTemporary->getPathname());
        }
    }
}
