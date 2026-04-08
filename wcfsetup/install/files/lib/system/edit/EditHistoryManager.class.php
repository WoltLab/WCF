<?php

namespace wcf\system\edit;

use wcf\data\edit\history\entry\EditHistoryEntryList;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Manages the edit history.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class EditHistoryManager extends SingletonFactory
{
    /**
     * list of available object types
     * @var array<string, ObjectType>
     */
    protected $availableObjectTypes = [];

    #[\Override]
    protected function init()
    {
        // get available object types
        $this->availableObjectTypes = ObjectTypeCache::getInstance()
            ->getObjectTypes('com.woltlab.wcf.edit.historySavingObject');
    }

    /**
     * Returns the id of the object type with the given name.
     *
     * @return int
     * @throws SystemException
     */
    public function getObjectTypeID(string $objectType)
    {
        if (!isset($this->availableObjectTypes[$objectType])) {
            throw new SystemException("unknown object type '" . $objectType . "'");
        }

        return $this->availableObjectTypes[$objectType]->objectTypeID;
    }

    /**
     * Adds a new entry.
     *
     * @param int $obsoletedByUserID The userID of the user that forced this entry to become outdated
     * @return void
     */
    public function add(string $objectType, int $objectID, string $message, int $time, int $userID, string $username, string $editReason, int $obsoletedByUserID)
    {
        // no op, if edit history is disabled
        if (!MODULE_EDIT_HISTORY) {
            return;
        }

        // save new entry
        $sql = "INSERT INTO wcf1_edit_history_entry
                            (objectTypeID, objectID, message, time, obsoletedAt, userID, username, editReason, obsoletedByUserID)
                VALUES      (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $this->getObjectTypeID($objectType),
            $objectID,
            $message,
            $time,
            TIME_NOW,
            $userID,
            $username,
            $editReason,
            $obsoletedByUserID,
        ]);
    }

    /**
     * Deletes edit history entries.
     *
     * @param int[] $objectIDs
     * @return void
     */
    public function delete(string $objectType, array $objectIDs)
    {
        $objectTypeID = $this->getObjectTypeID($objectType);

        $itemsPerLoop = 1000;
        $loopCount = \ceil(\count($objectIDs) / $itemsPerLoop);

        WCF::getDB()->beginTransaction();
        for ($i = 0; $i < $loopCount; $i++) {
            $batchObjectIDs = \array_slice($objectIDs, $i * $itemsPerLoop, $itemsPerLoop);

            $conditionBuilder = new PreparedStatementConditionBuilder();
            $conditionBuilder->add('objectTypeID = ?', [$objectTypeID]);
            $conditionBuilder->add('objectID IN (?)', [$batchObjectIDs]);

            $sql = "DELETE FROM wcf1_edit_history_entry
                    " . $conditionBuilder;
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($conditionBuilder->getParameters());
        }
        WCF::getDB()->commitTransaction();
    }

    /**
     * Performs mass reverting of edits by the given users in the given timeframe.
     *
     * @param int[] $userIDs
     * @return void
     */
    public function bulkRevert(array $userIDs, int $timeframe = 86400)
    {
        if (empty($userIDs)) {
            return;
        }

        // 1: Select the newest edit history item for each object ("newestEntries")
        // 2: Check whether the edit was made by the offending users ("vandalizedEntries")
        // 3: Fetch the newest version that is either:
        //    a) older than $timeframe days
        //    b) by a non offending user
        $userIDPlaceholders = '?' . \str_repeat(',?', \count($userIDs) - 1);
        $sql = "SELECT      MAX(entryID)
                FROM        wcf1_edit_history_entry revertTo
                INNER JOIN (
                    SELECT      vandalizedEntries.objectID,
                                vandalizedEntries.objectTypeID
                    FROM        wcf1_edit_history_entry vandalizedEntries
                    INNER JOIN (
                        SELECT      MAX(newestEntries.entryID) AS entryID
                        FROM        wcf1_edit_history_entry newestEntries
                        WHERE       newestEntries.obsoletedAt > ?
                        GROUP BY    newestEntries.objectTypeID, newestEntries.objectID
                    ) newestEntries2
                    WHERE       newestEntries2.entryID = vandalizedEntries.entryID
                            AND vandalizedEntries.obsoletedByUserID IN (" . $userIDPlaceholders . ")
                ) AS vandalizedEntries2
                WHERE       revertTo.objectID = vandalizedEntries2.objectID
                        AND revertTo.objectTypeID = vandalizedEntries2.objectTypeID
                        AND (
                                    revertTo.obsoletedAt <= ?
                                 OR revertTo.time <= ?
                                 OR revertTo.userID NOT IN(" . $userIDPlaceholders . ")
                            )
                GROUP BY    revertTo.objectTypeID, revertTo.objectID";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute(\array_merge(
            [TIME_NOW - $timeframe],
            $userIDs,
            [TIME_NOW - $timeframe],
            [TIME_NOW - $timeframe],
            $userIDs
        ));

        $entryIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
        if (empty($entryIDs)) {
            return;
        }

        $list = new EditHistoryEntryList();
        $list->getConditionBuilder()->add('entryID IN(?)', [$entryIDs]);
        $list->readObjects();
        foreach ($list as $entry) {
            $entry->getObject()->revertVersion($entry);
        }
    }
}
