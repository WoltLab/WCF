<?php

namespace wcf\data\attachment;

use wcf\data\object\type\ObjectTypeCache;
use wcf\system\attachment\IAttachmentObjectType;

/**
 * Represents a list of attachments.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.3 No longer in use.
 *
 * @extends AttachmentList<AdministrativeAttachment>
 */
class AdministrativeAttachmentList extends AttachmentList
{
    /**
     * @inheritDoc
     */
    public $decoratorClassName = AdministrativeAttachment::class;

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
            $processor = ObjectTypeCache::getInstance()->getObjectType($objectTypeID)->getProcessor();
            \assert($processor instanceof IAttachmentObjectType);
            $processor->cacheObjects($objectIDs);
        }
    }
}
