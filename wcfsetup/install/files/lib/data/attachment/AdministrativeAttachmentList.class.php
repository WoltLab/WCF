<?php

namespace wcf\data\attachment;

use wcf\data\object\type\ObjectTypeCache;
use wcf\system\WCF;

/**
 * Represents a list of attachments.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  AdministrativeAttachment    current()
 * @method  AdministrativeAttachment[]  getObjects()
 * @method  AdministrativeAttachment|null   getSingleObject()
 * @method  AdministrativeAttachment|null   search($objectID)
 * @property    AdministrativeAttachment[] $objects
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
        $this->sqlJoins = "
            LEFT JOIN   wcf1_user user_table
            ON          user_table.userID = attachment.userID";
    }

    /**
     * @inheritDoc
     */
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

    /**
     * Returns a list of available mime types.
     *
     * @return  string[]
     */
    public function getAvailableFileTypes()
    {
        $fileTypes = [];
        $sql = "SELECT  DISTINCT attachment.fileType
                FROM    wcf1_attachment attachment
                " . $this->getConditionBuilder();
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($this->getConditionBuilder()->getParameters());
        while ($row = $statement->fetchArray()) {
            if ($row['fileType']) {
                $fileTypes[$row['fileType']] = $row['fileType'];
            }
        }

        \ksort($fileTypes);

        return $fileTypes;
    }

    /**
     * Returns attachment statistics.
     *
     * @return  int[]
     */
    public function getStats()
    {
        $sql = "SELECT  COUNT(*) AS count,
                        COALESCE(SUM(attachment.filesize), 0) AS size,
                        COALESCE(SUM(downloads), 0) AS downloads
                FROM    wcf1_attachment attachment
                " . $this->getConditionBuilder();
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($this->getConditionBuilder()->getParameters());

        return $statement->fetchArray();
    }
}
