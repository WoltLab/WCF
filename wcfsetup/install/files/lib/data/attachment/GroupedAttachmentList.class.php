<?php

namespace wcf\data\attachment;

use wcf\data\object\type\ObjectTypeCache;

/**
 * Represents a grouped list of attachments.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class GroupedAttachmentList extends AttachmentList
{
    /**
     * grouped objects
     * @var array
     */
    public $groupedObjects = [];

    /**
     * object type
     * @var \wcf\data\object\type\ObjectType
     */
    protected $objectType;

    /**
     * @inheritDoc
     */
    public $sqlLimit = 0;

    /**
     * @inheritDoc
     */
    public $sqlOrderBy = 'attachment.showOrder';

    /**
     * Creates a new GroupedAttachmentList object.
     *
     * @param string $objectType
     */
    public function __construct($objectType)
    {
        parent::__construct();

        $this->objectType = ObjectTypeCache::getInstance()->getObjectTypeByName(
            'com.woltlab.wcf.attachment.objectType',
            $objectType
        );
        $this->getConditionBuilder()->add('attachment.objectTypeID = ?', [$this->objectType->objectTypeID]);

        $this->getConditionBuilder()->add(
            '(
                SELECT  DISTINCT embeddedObjectID
                FROM    wcf1_message_embedded_object
                WHERE   messageObjectTypeID = ?
                    AND messageID = attachment.objectID
                    AND embeddedObjectTypeID = ?
                    AND embeddedObjectID = attachment.attachmentID
            ) IS NULL',
            [
                ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.message', $objectType),
                ObjectTypeCache::getInstance()->getObjectTypeIDByName(
                    'com.woltlab.wcf.message.embeddedObject',
                    'com.woltlab.wcf.attachment'
                ),
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function readObjects()
    {
        parent::readObjects();

        // group by object id
        foreach ($this->objects as $attachmentID => $attachment) {
            if (!isset($this->groupedObjects[$attachment->objectID])) {
                $this->groupedObjects[$attachment->objectID] = [];
            }

            $this->groupedObjects[$attachment->objectID][$attachmentID] = $attachment;
        }
    }

    /**
     * Sets the permissions for attachment access.
     *
     * @param bool[] $permissions
     */
    public function setPermissions(array $permissions)
    {
        foreach ($this->objects as $attachment) {
            $attachment->setPermissions($permissions);
        }
    }

    /**
     * Returns the objects of the list.
     *
     * @param int $objectID
     * @return  Attachment[]
     */
    public function getGroupedObjects($objectID)
    {
        if (isset($this->groupedObjects[$objectID])) {
            return $this->groupedObjects[$objectID];
        }

        return [];
    }
}
