<?php

namespace wcf\command\attachment;

use wcf\data\attachment\Attachment;
use wcf\data\attachment\AttachmentBuilder;
use wcf\data\attachment\AttachmentList;
use wcf\data\file\thumbnail\FileThumbnailList;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\file\processor\FileProcessor;

/**
 * Copies attachments from one object to another.
 * Returns an array of old attachmentIDs as keys and new attachmentIDs as values.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class CopyAttachments
{
    public function __construct(
        private readonly string $sourceObjectType,
        private readonly int $sourceObjectID,
        private readonly string $targetObjectType,
        private readonly int $targetObjectID,
    ) {}

    /**
     * @return array<int, int>
     */
    public function __invoke(): array
    {
        $sourceObjectType = ObjectTypeCache::getInstance()->getObjectTypeByName(
            'com.woltlab.wcf.attachment.objectType',
            $this->sourceObjectType
        );
        $targetObjectType = ObjectTypeCache::getInstance()->getObjectTypeByName(
            'com.woltlab.wcf.attachment.objectType',
            $this->targetObjectType
        );

        $attachments = $this->getAttachments($sourceObjectType, $this->sourceObjectID);

        $newAttachmentIDs = [];
        foreach ($attachments as $attachment) {
            $newAttachment = $this->copyAttachment($targetObjectType, $attachment);

            $newAttachmentIDs[$attachment->attachmentID] = $newAttachment->attachmentID;
        }

        return $newAttachmentIDs;
    }

    /**
     * @return Attachment[]
     */
    private function getAttachments(ObjectType $sourceObjectType, int $sourceObjectID): array
    {
        $attachmentList = new AttachmentList();
        $attachmentList->getConditionBuilder()->add("attachment.objectTypeID = ?", [$sourceObjectType->objectTypeID]);
        $attachmentList->getConditionBuilder()->add("attachment.objectID = ?", [$sourceObjectID]);
        $attachmentList->readObjects();

        return $attachmentList->getObjects();
    }

    private function copyAttachment(ObjectType $targetObjectType, Attachment $oldAttachment): Attachment
    {
        $file = $oldAttachment->getFile();
        $thumbnailID = null;
        $tinyThumbnailID = null;

        if ($file !== null) {
            $file = FileProcessor::getInstance()->copy($file, 'com.woltlab.wcf.attachment');

            if ($oldAttachment->thumbnailID !== null || $oldAttachment->tinyThumbnailID !== null) {
                $thumbnailList = new FileThumbnailList();
                $thumbnailList->getConditionBuilder()->add('fileID = ?', [$file->fileID]);
                $thumbnailList->readObjects();

                foreach ($thumbnailList->getObjects() as $thumbnail) {
                    match ($thumbnail->identifier) {
                        '' => $thumbnailID = $thumbnail->thumbnailID,
                        'tiny' => $tinyThumbnailID = $thumbnail->thumbnailID,
                    };
                }
            }
        }

        $builder = AttachmentBuilder::forCreate()
            ->setObjectType($targetObjectType)
            ->setObjectID($this->targetObjectID)
            ->setUserID($oldAttachment->userID)
            ->setDownloads($oldAttachment->downloads)
            ->setLastDownloadTime($oldAttachment->lastDownloadTime)
            ->setUploadTime($oldAttachment->uploadTime)
            ->setShowOrder($oldAttachment->showOrder)
            ->setFile($file)
            ->setThumbnailID($thumbnailID)
            ->setTinyThumbnailID($tinyThumbnailID);

        return new CreateAttachment($builder)();
    }
}
