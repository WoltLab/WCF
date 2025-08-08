<?php

namespace wcf\data\attachment;

use wcf\command\attachment\CopyAttachments;
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
 * @extends AbstractDatabaseObjectAction<Attachment, AttachmentEditor>
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
     * @var mixed[]
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
     * @return void
     * @deprecated 6.1
     */
    public function generateThumbnails()
    {
        // Does nothing.
    }

    /**
     * Copies attachments from one object id to another.
     *
     * @return array{attachmentIDs: array<int, int>}
     *
     * @deprecated 6.3 use `CopyAttachments` instead.
     */
    public function copy()
    {
        return [
            'attachmentIDs' => (new CopyAttachments(
                $this->parameters['sourceObjectType'],
                $this->parameters['sourceObjectID'],
                $this->parameters['targetObjectType'],
                $this->parameters['targetObjectID']
            ))()
        ];
    }
}
