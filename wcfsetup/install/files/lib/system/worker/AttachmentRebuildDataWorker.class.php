<?php

namespace wcf\system\worker;

use wcf\data\attachment\Attachment;
use wcf\data\attachment\AttachmentEditor;
use wcf\data\attachment\AttachmentList;
use wcf\data\file\FileEditor;
use wcf\data\file\thumbnail\FileThumbnailList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\runtime\ArticleRuntimeCache;
use wcf\system\file\processor\FileProcessor;
use wcf\system\WCF;

/**
 * Worker implementation for updating attachments.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractLinearRebuildDataWorker<AttachmentList>
 * @deprecated 6.1 Should be removed in 6.2 as its only purpose is to migrate to the new upload API.
 */
class AttachmentRebuildDataWorker extends AbstractLinearRebuildDataWorker
{
    /**
     * @inheritDoc
     */
    protected $objectListClassName = AttachmentList::class;

    /**
     * @inheritDoc
     */
    protected $limit = 100;

    #[\Override]
    public function execute()
    {
        parent::execute();

        /** @var array<int,int> */
        $attachmentToFileID = [];

        /** @var list<int> */
        $defunctAttachmentIDs = [];

        $articleObjectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName(
            'com.woltlab.wcf.attachment.objectType',
            'com.woltlab.wcf.article'
        );

        foreach ($this->objectList as $attachment) {
            if ($attachment->fileID !== null) {
                $this->removeThumbnails($attachment);

                continue;
            }

            $attachment->migrateStorage();

            $file = FileEditor::createFromExistingFile(
                $attachment->getLocation(),
                $attachment->filename,
                'com.woltlab.wcf.attachment'
            );

            if ($file === null) {
                $defunctAttachmentIDs[] = $attachment->attachmentID;
                continue;
            }

            $attachmentToFileID[$attachment->attachmentID] = $file->fileID;
            $this->removeThumbnails($attachment);

            if ($attachment->objectTypeID === $articleObjectTypeID) {
                $this->migrateArticleAttachment($attachment);
            }
        }

        $this->setFileIDs($attachmentToFileID);
        $this->removeDefunctAttachments($defunctAttachmentIDs);
    }

    /**
     * @param array<int,int> $attachmentToFileID
     */
    private function setFileIDs(array $attachmentToFileID): void
    {
        if ($attachmentToFileID === []) {
            return;
        }

        $sql = "UPDATE  wcf1_attachment
                SET     fileID = ?
                WHERE   attachmentID = ?";
        $statement = WCF::getDB()->prepare($sql);

        WCF::getDB()->beginTransaction();
        foreach ($attachmentToFileID as $attachmentID => $fileID) {
            $statement->execute([
                $fileID,
                $attachmentID,
            ]);
        }
        WCF::getDB()->commitTransaction();
    }

    /**
     * @param list<int> $attachmentIDs
     */
    private function removeDefunctAttachments(array $attachmentIDs): void
    {
        if ($attachmentIDs === []) {
            return;
        }

        AttachmentEditor::deleteAll($attachmentIDs);
    }

    private function removeThumbnails(Attachment $attachment): void
    {
        if ($attachment->thumbnailType) {
            $filepath = $attachment->getThumbnailLocation();
            if (\file_exists($filepath)) {
                \unlink($filepath);
            }
        }

        if ($attachment->tinyThumbnailType) {
            $filepath = $attachment->getTinyThumbnailLocation();
            if (\file_exists($filepath)) {
                \unlink($filepath);
            }
        }
    }

    private function migrateArticleAttachment(Attachment $attachment): void
    {
        $article = ArticleRuntimeCache::getInstance()->getObject($attachment->objectID);
        if ($article === null) {
            return;
        }

        $copyAttachments = false;
        foreach ($article->getArticleContents() as $content) {
            if ($copyAttachments) {
                $this->copyAttachment(
                    $attachment,
                    'com.woltlab.wcf.article.content',
                    $content->getObjectID()
                );
            } else {
                (new AttachmentEditor($attachment))->update([
                    'objectTypeID' => ObjectTypeCache::getInstance()->getObjectTypeIDByName(
                        'com.woltlab.wcf.attachment.objectType',
                        'com.woltlab.wcf.article.content'
                    ),
                    'objectID' => $content->getObjectID(),
                ]);

                $copyAttachments = true;
            }
        }
    }

    private function copyAttachment(
        Attachment $oldAttachment,
        string $targetObjectType,
        int $targetObjectID
    ): void {
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
                    // @phpstan-ignore match.unhandled
                    match ($thumbnail->identifier) {
                        '' => $thumbnailID = $thumbnail->thumbnailID,
                        'tiny' => $tinyThumbnailID = $thumbnail->thumbnailID,
                    };
                }
            }
        }

        AttachmentEditor::create([
            'objectTypeID' => ObjectTypeCache::getInstance()->getObjectTypeIDByName(
                'com.woltlab.wcf.attachment.objectType',
                $targetObjectType
            ),
            'objectID' => $targetObjectID,
            'userID' => $oldAttachment->userID,
            'filename' => $oldAttachment->filename,
            'filesize' => $oldAttachment->filesize,
            'fileType' => $oldAttachment->fileType,
            'fileHash' => $oldAttachment->fileHash,
            'isImage' => $oldAttachment->isImage,
            'width' => $oldAttachment->width,
            'height' => $oldAttachment->height,
            'tinyThumbnailType' => $oldAttachment->tinyThumbnailType,
            'tinyThumbnailSize' => $oldAttachment->tinyThumbnailSize,
            'tinyThumbnailWidth' => $oldAttachment->tinyThumbnailWidth,
            'tinyThumbnailHeight' => $oldAttachment->tinyThumbnailHeight,
            'thumbnailType' => $oldAttachment->thumbnailType,
            'thumbnailSize' => $oldAttachment->thumbnailSize,
            'thumbnailWidth' => $oldAttachment->thumbnailWidth,
            'thumbnailHeight' => $oldAttachment->thumbnailHeight,
            'downloads' => $oldAttachment->downloads,
            'lastDownloadTime' => $oldAttachment->lastDownloadTime,
            'uploadTime' => $oldAttachment->uploadTime,
            'showOrder' => $oldAttachment->showOrder,
            'fileID' => $file?->fileID,
            'thumbnailID' => $thumbnailID,
            'tinyThumbnailID' => $tinyThumbnailID,
        ]);
    }
}
