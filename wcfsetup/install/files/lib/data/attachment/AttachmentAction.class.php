<?php

namespace wcf\data\attachment;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Executes attachment-related actions.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  Attachment      create()
 * @method  AttachmentEditor[]  getObjects()
 * @method  AttachmentEditor    getSingleObject()
 */
class AttachmentAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = AttachmentEditor::class;

    /**
     * current attachment object, used to communicate with event listeners
     * @var Attachment
     */
    public $eventAttachment;

    /**
     * current data, used to communicate with event listeners.
     * @var array
     */
    public $eventData = [];

    /**
     * @inheritDoc
     */
    public function validateDelete()
    {
        WCF::getSession()->checkPermissions(['admin.attachment.canManageAttachment']);

        if (empty($this->objects)) {
            $this->readObjects();

            if (empty($this->objects)) {
                throw new UserInputException('objectIDs');
            }
        }

        foreach ($this->getObjects() as $attachment) {
            if (ObjectTypeCache::getInstance()->getObjectType($attachment->objectTypeID)->private) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * Generates thumbnails.
     *
     * @deprecated 6.1
     */
    public function generateThumbnails()
    {
        // Does nothing.
    }

    /**
     * Copies attachments from one object id to another.
     */
    public function copy()
    {
        $sourceObjectType = ObjectTypeCache::getInstance()->getObjectTypeByName(
            'com.woltlab.wcf.attachment.objectType',
            $this->parameters['sourceObjectType']
        );
        $targetObjectType = ObjectTypeCache::getInstance()->getObjectTypeByName(
            'com.woltlab.wcf.attachment.objectType',
            $this->parameters['targetObjectType']
        );

        $attachmentList = new AttachmentList();
        $attachmentList->getConditionBuilder()->add("attachment.objectTypeID = ?", [$sourceObjectType->objectTypeID]);
        $attachmentList->getConditionBuilder()->add("attachment.objectID = ?", [$this->parameters['sourceObjectID']]);
        $attachmentList->readObjects();

        $newAttachmentIDs = [];
        foreach ($attachmentList as $attachment) {
            $newAttachment = AttachmentEditor::create([
                'objectTypeID' => $targetObjectType->objectTypeID,
                'objectID' => $this->parameters['targetObjectID'],
                'userID' => $attachment->userID,
                'filename' => $attachment->filename,
                'filesize' => $attachment->filesize,
                'fileType' => $attachment->fileType,
                'fileHash' => $attachment->fileHash,
                'isImage' => $attachment->isImage,
                'width' => $attachment->width,
                'height' => $attachment->height,
                'tinyThumbnailType' => $attachment->tinyThumbnailType,
                'tinyThumbnailSize' => $attachment->tinyThumbnailSize,
                'tinyThumbnailWidth' => $attachment->tinyThumbnailWidth,
                'tinyThumbnailHeight' => $attachment->tinyThumbnailHeight,
                'thumbnailType' => $attachment->thumbnailType,
                'thumbnailSize' => $attachment->thumbnailSize,
                'thumbnailWidth' => $attachment->thumbnailWidth,
                'thumbnailHeight' => $attachment->thumbnailHeight,
                'downloads' => $attachment->downloads,
                'lastDownloadTime' => $attachment->lastDownloadTime,
                'uploadTime' => $attachment->uploadTime,
                'showOrder' => $attachment->showOrder,
            ]);

            // copy attachment
            @\copy($attachment->getLocation(), $newAttachment->getLocation());

            if ($attachment->tinyThumbnailSize) {
                @\copy($attachment->getTinyThumbnailLocation(), $newAttachment->getTinyThumbnailLocation());
            }
            if ($attachment->thumbnailSize) {
                @\copy($attachment->getThumbnailLocation(), $newAttachment->getThumbnailLocation());
            }

            $newAttachmentIDs[$attachment->attachmentID] = $newAttachment->attachmentID;
        }

        return [
            'attachmentIDs' => $newAttachmentIDs,
        ];
    }
}
