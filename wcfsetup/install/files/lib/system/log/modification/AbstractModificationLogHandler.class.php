<?php

namespace wcf\system\log\modification;

use wcf\data\modification\log\ModificationLog;
use wcf\data\modification\log\ModificationLogAction;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Abstract implementation of a modification log handler for a certain modifiable content object type.
 *
 * @author  Alexander Ebert, Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
abstract class AbstractModificationLogHandler extends SingletonFactory
{
    /**
     * modifiable content object type
     * @var ObjectType
     */
    protected $objectType;

    /**
     * name of the modifiable content object type
     * @var string
     */
    protected $objectTypeName = '';

    /**
     * @inheritDoc
     * @throws  SystemException
     */
    protected function init()
    {
        $this->objectType = ObjectTypeCache::getInstance()
            ->getObjectTypeByName('com.woltlab.wcf.modifiableContent', $this->objectTypeName);
        if ($this->objectType === null) {
            throw new SystemException("Object type '" . $this->objectTypeName . "' not found within definition 'com.woltlab.wcf.modifiableContent'");
        }
    }

    /**
     * Creates a modification log entry.
     *
     * @param string $action
     * @param int $objectID
     * @param int|null $parentObjectID
     * @param array $additionalData
     * @param int $time
     * @param int|null $userID
     * @param string|null $username
     * @param int $hidden
     * @return  ModificationLog
     */
    public function createLog(
        $action,
        $objectID,
        $parentObjectID = null,
        array $additionalData = [],
        $time = TIME_NOW,
        $userID = null,
        $username = null,
        $hidden = 1
    ) {
        // set default user data
        if ($userID === null) {
            if (WCF::getUser()->userID) {
                $userID = WCF::getUser()->userID;
            } elseif ($username === null) {
                $username = 'System';
            }
        }
        if ($username === null) {
            if (WCF::getUser()->username) {
                $username = WCF::getUser()->username;
            } else {
                $username = '';
            }
        }

        $modificationLogAction = new ModificationLogAction([], 'create', [
            'data' => [
                'objectTypeID' => $this->objectType->objectTypeID,
                'objectID' => $objectID,
                'parentObjectID' => $parentObjectID,
                'action' => $action,
                'userID' => $userID,
                'username' => $username,
                'time' => $time,
                'hidden' => $hidden,
                'additionalData' => \serialize($additionalData),
            ],
        ]);

        return $modificationLogAction->executeAction()['returnValues'];
    }

    /**
     * Deletes modification log entries.
     *
     * @param int[] $objectIDs
     * @param string[] $ignoredActions names of actions whose log entries will not be deleted
     */
    public function deleteLogs(array $objectIDs, array $ignoredActions = [])
    {
        if (empty($objectIDs)) {
            return;
        }

        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('objectTypeID = ?', [$this->objectType->objectTypeID]);
        $conditionBuilder->add('objectID IN (?)', [$objectIDs]);
        if (!empty($ignoredActions)) {
            $conditionBuilder->add('action NOT IN (?)', [$ignoredActions]);
        }

        $sql = "DELETE FROM wcf1_modification_log
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());
    }

    /**
     * Deletes modification log entries by the id of the parent object.
     *
     * @param int[] $parentObjectIDs
     */
    public function deleteLogsByParentIDs(array $parentObjectIDs)
    {
        if (empty($parentObjectIDs)) {
            return;
        }

        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('objectTypeID = ?', [$this->objectType->objectTypeID]);
        $conditionBuilder->add('parentObjectID IN (?)', [$parentObjectIDs]);

        $sql = "DELETE FROM wcf1_modification_log
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());
    }

    /**
     * Returns the modifiable content object type.
     *
     * @param string $objectType name of the modifiable content object type, deprecated parameter
     * @return  ObjectType
     */
    public function getObjectType($objectType = null)
    {
        // allow parameter for better backwards compatibility with ModificationLogHandler
        if ($objectType !== null) {
            return ObjectTypeCache::getInstance()
                ->getObjectTypeByName('com.woltlab.wcf.modifiableContent', $objectType);
        }

        return $this->objectType;
    }

    /**
     * Updates the parent object id of modification log entries.
     *
     * @param int[] $objectIDs
     * @param int $newParentObjectID
     */
    public function updateParentObjectID(array $objectIDs, $newParentObjectID)
    {
        if (empty($objectIDs)) {
            return;
        }

        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('objectTypeID = ?', [$this->objectType->objectTypeID]);
        $conditionBuilder->add('objectID IN (?)', [$objectIDs]);

        $sql = "UPDATE  wcf1_modification_log
                SET     parentObjectID = ?
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute(\array_merge([$newParentObjectID], $conditionBuilder->getParameters()));
    }
}
