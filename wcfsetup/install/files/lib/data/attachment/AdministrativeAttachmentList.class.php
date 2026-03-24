<?php

namespace wcf\data\attachment;

use wcf\data\object\type\ObjectTypeCache;

/**
 * Represents a list of attachments.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AttachmentList<AdministrativeAttachment>
 */
class AdministrativeAttachmentList extends AttachmentList
{
    /**
     * @inheritDoc
     */
    public $decoratorClassName = AdministrativeAttachment::class;

    /**
     * Creates a new AdministrativeAttachmentList object.
     */
    public function __construct()
    {
        parent::__construct();

        $this->sqlSelects = 'user_table.username';

        $join = "LEFT JOIN   wcf1_user user_table
                 ON          user_table.userID = attachment.userID
                 LEFT JOIN   wcf1_file file_table
                 ON          file_table.fileID = attachment.fileID";

        $this->sqlJoins = $join;
        $this->sqlConditionJoins = $join;
    }

    #[\Override]
    public function readObjects()
    {
        parent::readObjects();

        // cache objects
        $groupedObjectIDs = [];
        foreach ($this->objects as $attachment) {
            if (!isset($groupedObjectIDs[$attachment->objectTypeID])) {
                $groupedObjectIDs[$attachment->objectTypeID] = [];
            }
            $groupedObjectIDs[$attachment->objectTypeID][] = $attachment->objectID;
        }

        foreach ($groupedObjectIDs as $objectTypeID => $objectIDs) {
            $objectType = ObjectTypeCache::getInstance()->getObjectType($objectTypeID);
            $objectType->getProcessor()->cacheObjects($objectIDs);
        }
    }
}
