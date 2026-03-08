<?php

namespace wcf\data\attachment;

use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;

/**
 * Represents a grouped list of attachments.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class GroupedAttachmentList extends AttachmentList
{
    /**
     * @var array<int, array<int, Attachment>>
     */
    public array $groupedObjects = [];

    protected ObjectType $objectType;

    /**
     * @inheritDoc
     */
    public $sqlLimit = 0;

    /**
     * @inheritDoc
     */
    public $sqlOrderBy = 'attachment.showOrder';

    public function __construct(string $objectType)
    {
        parent::__construct();

        $objectTypeObj = ObjectTypeCache::getInstance()->getObjectTypeByName(
            'com.woltlab.wcf.attachment.objectType',
            $objectType
        );
        if ($objectType === null) {
            throw new \BadMethodCallException("unknown attachment object type '{$objectType}'");
        }

        $this->objectType = $objectTypeObj;
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

    #[\Override]
    public function readObjects(): void
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
     * @param array<string, bool> $permissions
     */
    public function setPermissions(array $permissions): void
    {
        foreach ($this->objects as $attachment) {
            $attachment->setPermissions($permissions);
        }
    }

    /**
     * Returns the attachments associated with the given objectID.
     *
     * @return Attachment[]
     */
    public function getGroupedObjects(int $objectID): array
    {
        if (isset($this->groupedObjects[$objectID])) {
            return $this->groupedObjects[$objectID];
        }

        return [];
    }

    /**
     * @since 6.2
     */
    public function getObjectTypeName(): string
    {
        return $this->objectType->objectType;
    }
}
