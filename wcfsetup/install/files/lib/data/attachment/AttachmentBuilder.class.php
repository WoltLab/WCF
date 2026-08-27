<?php

namespace wcf\data\attachment;

use wcf\data\DatabaseObjectBuilder;
use wcf\data\file\File;
use wcf\data\file\FileEditor;
use wcf\data\file\thumbnail\FileThumbnail;
use wcf\data\object\type\ObjectType;
use wcf\data\user\User;
use wcf\system\file\processor\exception\UnexpectedThumbnailIdentifier;

/**
 * Builder for creating, updating and deleting attachments.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<Attachment>
 */
final class AttachmentBuilder extends DatabaseObjectBuilder
{
    public function setObjectType(ObjectType $objectType): static
    {
        return $this->setObjectTypeID($objectType->objectTypeID);
    }

    public function setObjectTypeID(int $objectTypeID): static
    {
        $this->properties['objectTypeID'] = $objectTypeID;

        return $this;
    }

    public function setObjectID(?int $objectID): static
    {
        $this->properties['objectID'] = $objectID;

        return $this;
    }

    public function setUser(?User $user): static
    {
        return $this->setUserID($user?->userID);
    }

    public function setUserID(?int $userID): static
    {
        $this->properties['userID'] = $userID;

        return $this;
    }

    /**
     * Sets the temporary hash that is used to identify attachments that have not
     * been assigned to an object yet. Use an empty string for attachments that
     * belong to an existing object.
     */
    public function setTmpHash(string $tmpHash): static
    {
        $this->properties['tmpHash'] = $tmpHash;

        return $this;
    }

    public function setFile(?File $file): static
    {
        $this->properties['fileID'] = $file?->fileID;

        return $this;
    }

    /**
     * Assigns the thumbnail to the column that matches its identifier.
     *
     * @throws UnexpectedThumbnailIdentifier if the identifier is not supported by attachments
     */
    public function setThumbnail(FileThumbnail $thumbnail): static
    {
        return match ($thumbnail->identifier) {
            '' => $this->setThumbnailID($thumbnail->thumbnailID),
            'tiny' => $this->setTinyThumbnailID($thumbnail->thumbnailID),
            default => throw new UnexpectedThumbnailIdentifier($thumbnail->identifier),
        };
    }

    public function setThumbnailID(?int $thumbnailID): static
    {
        $this->properties['thumbnailID'] = $thumbnailID;

        return $this;
    }

    public function setTinyThumbnailID(?int $tinyThumbnailID): static
    {
        $this->properties['tinyThumbnailID'] = $tinyThumbnailID;

        return $this;
    }

    public function setUploadTime(int $uploadTime): static
    {
        $this->properties['uploadTime'] = $uploadTime;

        return $this;
    }

    public function setShowOrder(int $showOrder): static
    {
        $this->properties['showOrder'] = $showOrder;

        return $this;
    }

    public function setDownloads(int $downloads): static
    {
        $this->properties['downloads'] = $downloads;

        return $this;
    }

    public function incrementDownloads(int $downloads): static
    {
        $this->incrementProperties['downloads'] = $downloads;

        return $this;
    }

    public function setLastDownloadTime(int $lastDownloadTime): static
    {
        $this->properties['lastDownloadTime'] = $lastDownloadTime;

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['objectTypeID'];
    }

    #[\Override]
    protected static function beforeDeleteAll(array $objectIDs): void
    {
        $attachmentList = new AttachmentList();
        $attachmentList->getConditionBuilder()->add('attachment.attachmentID IN (?)', [$objectIDs]);
        $attachmentList->readObjects();

        $fileIDs = [];
        foreach ($attachmentList as $attachment) {
            if ($attachment->fileID !== null) {
                $fileIDs[] = $attachment->fileID;
            } else {
                self::deleteLegacyFiles($attachment);
            }
        }

        if ($fileIDs !== []) {
            FileEditor::deleteAll($fileIDs);
        }
    }

    /**
     * @deprecated 6.3 This will no longer be required once the attachments have been migrated.
     */
    private static function deleteLegacyFiles(Attachment $attachment): void
    {
        @\unlink($attachment->getLocation());

        if ($attachment->tinyThumbnailType !== '') {
            @\unlink($attachment->getTinyThumbnailLocation());
        }
        if ($attachment->thumbnailType !== '') {
            @\unlink($attachment->getThumbnailLocation());
        }
    }
}
