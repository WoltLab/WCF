<?php

namespace wcf\system\worker;

use wcf\data\attachment\Attachment;
use wcf\data\attachment\AttachmentEditor;
use wcf\data\attachment\AttachmentList;
use wcf\data\file\FileEditor;
use wcf\system\WCF;

/**
 * Worker implementation for updating attachments.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  AttachmentList  getObjectList()
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

        foreach ($this->objectList as $attachment) {
            \assert($attachment instanceof Attachment);

            if ($attachment->fileID !== null) {
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
}
