<?php

namespace wcf\data\attachment;

use wcf\data\DatabaseObjectList;
use wcf\data\file\FileList;
use wcf\data\file\thumbnail\FileThumbnailList;

/**
 * Represents a list of attachments.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  Attachment      current()
 * @method  Attachment[]        getObjects()
 * @method  Attachment|null     getSingleObject()
 * @method  Attachment|null     search($objectID)
 * @property    Attachment[] $objects
 */
class AttachmentList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = Attachment::class;

    public $enableFileLoading = true;

    #[\Override]
    public function readObjects()
    {
        parent::readObjects();

        if ($this->enableFileLoading) {
            $this->loadFiles();
        }
    }

    private function loadFiles(): void
    {
        $fileIDs = [];
        foreach ($this->objects as $attachment) {
            if ($attachment->fileID) {
                $fileIDs[] = $attachment->fileID;
            }
        }

        if ($fileIDs === []) {
            return;
        }

        $fileList = new FileList();
        $fileList->getConditionBuilder()->add("fileID IN (?)", [$fileIDs]);
        $fileList->readObjects();
        $files = $fileList->getObjects();

        $thumbnailList = new FileThumbnailList();
        $thumbnailList->getConditionBuilder()->add("fileID IN (?)", [$fileIDs]);
        $thumbnailList->readObjects();
        foreach ($thumbnailList as $thumbnail) {
            $files[$thumbnail->fileID]->addThumbnail($thumbnail);
        }

        foreach ($this->objects as $attachment) {
            $file = $files[$attachment->fileID] ?? null;
            if ($file !== null) {
                $attachment->setFile($file);
            }
        }
    }
}
