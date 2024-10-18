<?php

namespace wcf\system\user\activity\event;

use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\activity\event\UserActivityEventAction;
use wcf\data\user\activity\event\ViewableUserActivityEventList;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * User activity event handler.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserActivityEventHandler extends SingletonFactory
{
    /**
     * cached object types
     * @var ObjectType[]
     */
    protected $objectTypes = [];

    /**
     * @inheritDoc
     */
    protected function init()
    {
        // load object types
        $cache = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.user.recentActivityEvent');
        foreach ($cache as $objectType) {
            $this->objectTypes['names'][$objectType->objectType] = $objectType->objectTypeID;
            $this->objectTypes['objects'][$objectType->objectTypeID] = $objectType;
        }
    }

    /**
     * Returns an object type by id.
     *
     * @param int $objectTypeID
     * @return  ObjectType|null
     */
    public function getObjectType($objectTypeID)
    {
        return $this->objectTypes['objects'][$objectTypeID] ?? null;
    }

    /**
     * Returns an object type id by object type name.
     *
     * @param string $objectType
     * @return  int|null
     */
    public function getObjectTypeID($objectType)
    {
        return $this->objectTypes['names'][$objectType] ?? null;
    }

    /**
     * Fires a new activity event.
     *
     * @param string $objectType
     * @param int $objectID
     * @param int $languageID
     * @param int $userID
     * @param int $time
     * @param array $additionalData
     * @return  \wcf\data\user\activity\event\UserActivityEvent
     * @throws  SystemException
     */
    public function fireEvent(
        $objectType,
        $objectID,
        $languageID = null,
        $userID = null,
        $time = TIME_NOW,
        $additionalData = []
    ) {
        $objectTypeID = $this->getObjectTypeID($objectType);
        if ($objectTypeID === null) {
            throw new SystemException("Unknown recent activity event '" . $objectType . "'");
        }

        if ($userID === null) {
            $userID = WCF::getUser()->userID;
        }

        $eventAction = new UserActivityEventAction([], 'create', [
            'data' => [
                'objectTypeID' => $objectTypeID,
                'objectID' => $objectID,
                'languageID' => $languageID,
                'userID' => $userID,
                'time' => $time,
                'additionalData' => \serialize($additionalData),
            ],
        ]);
        $returnValues = $eventAction->executeAction();

        return $returnValues['returnValues'];
    }

    /**
     * Fires multiple new activity events for the same activity event type.
     *
     * This method is intended for bulk processing.
     *
     * @param string $objectType
     * @param array $eventData
     * @throws  SystemException
     */
    public function fireEvents($objectType, array $eventData)
    {
        $objectTypeID = $this->getObjectTypeID($objectType);
        if ($objectTypeID === null) {
            throw new SystemException("Unknown recent activity event '" . $objectType . "'");
        }

        $itemsPerLoop = 1000;
        $loopCount = \ceil(\count($eventData) / $itemsPerLoop);

        WCF::getDB()->beginTransaction();
        for ($i = 0; $i < $loopCount; $i++) {
            $batchEventData = \array_slice($eventData, $i * $itemsPerLoop, $itemsPerLoop);

            $parameters = [];
            foreach ($batchEventData as $data) {
                $parameters = \array_merge($parameters, [
                    $objectTypeID,
                    $data['objectID'],
                    $data['languageID'] ?? null,
                    $data['userID'] ?? WCF::getUser()->userID,
                    $data['time'] ?? TIME_NOW,
                    \serialize($data['additionalData'] ?? []),
                ]);
            }

            $sql = "INSERT INTO wcf1_user_activity_event
                                (objectTypeID, objectID, languageID, userID, time, additionalData)
                    VALUES      (?, ?, ?, ?, ?, ?)" . \str_repeat(', (?, ?, ?, ?, ?, ?)', \count($batchEventData) - 1);
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($parameters);
        }
        WCF::getDB()->commitTransaction();
    }

    /**
     * Removes an activity event.
     *
     * @param int $objectType
     * @param int $objectID
     * @param int $userID
     * @throws  SystemException
     */
    public function removeEvent($objectType, $objectID, $userID = null)
    {
        $objectTypeID = $this->getObjectTypeID($objectType);
        if ($objectTypeID === null) {
            throw new SystemException("Unknown recent activity event '" . $objectType . "'");
        }

        if ($userID === null) {
            $userID = WCF::getUser()->userID;
        }

        $sql = "DELETE FROM wcf1_user_activity_event
                WHERE       objectTypeID = ?
                        AND objectID = ?
                        AND userID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $objectTypeID,
            $objectID,
            $userID,
        ]);
    }

    /**
     * Removes activity events.
     *
     * @param string $objectType
     * @param int[] $objectIDs
     * @throws  SystemException
     */
    public function removeEvents($objectType, array $objectIDs)
    {
        if (empty($objectIDs)) {
            return;
        }

        $objectTypeID = $this->getObjectTypeID($objectType);
        if ($objectTypeID === null) {
            throw new SystemException("Unknown recent activity event '" . $objectType . "'");
        }

        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("objectTypeID = ?", [$objectTypeID]);
        $conditions->add("objectID IN (?)", [$objectIDs]);

        $sql = "DELETE FROM wcf1_user_activity_event
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());
    }

    /**
     * Validates an event list and removes orphaned events.
     *
     * @param ViewableUserActivityEventList $eventList
     */
    public static function validateEvents(ViewableUserActivityEventList $eventList)
    {
        $eventIDs = $eventList->validateEvents();

        // remove orphaned event ids
        if (!empty($eventIDs)) {
            $sql = "DELETE FROM wcf1_user_activity_event
                    WHERE       eventID = ?";
            $statement = WCF::getDB()->prepare($sql);

            foreach ($eventIDs as $eventID) {
                $statement->execute([$eventID]);
            }
        }
    }
}
