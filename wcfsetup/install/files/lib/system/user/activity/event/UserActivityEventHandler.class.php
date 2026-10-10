<?php

namespace wcf\system\user\activity\event;

use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\activity\event\UserActivityEvent;
use wcf\data\user\activity\event\UserActivityEventAction;
use wcf\data\user\activity\event\ViewableUserActivityEventList;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * User activity event handler.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class UserActivityEventHandler extends SingletonFactory
{
    /**
     * @var array{names?: array<string, int>, objects?: array<int, ObjectType>}
     */
    private array $objectTypes = [];

    #[\Override]
    protected function init(): void
    {
        $cache = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.user.recentActivityEvent');
        foreach ($cache as $objectType) {
            $this->objectTypes['names'][$objectType->objectType] = $objectType->objectTypeID;
            $this->objectTypes['objects'][$objectType->objectTypeID] = $objectType;
        }
    }

    /**
     * Returns an object type by id.
     */
    public function getObjectType(int $objectTypeID): ?ObjectType
    {
        return $this->objectTypes['objects'][$objectTypeID] ?? null;
    }

    /**
     * Returns an object type id by object type name.
     */
    public function getObjectTypeID(string $objectType): ?int
    {
        return $this->objectTypes['names'][$objectType] ?? null;
    }

    /**
     * Fires a new activity event.
     *
     * @param mixed[] $additionalData
     */
    public function fireEvent(
        string $objectType,
        int $objectID,
        ?int $languageID = null,
        ?int $userID = null,
        int $time = \TIME_NOW,
        array $additionalData = [],
        ?string $username = null
    ): UserActivityEvent {
        $objectTypeID = $this->getObjectTypeID($objectType);
        if ($objectTypeID === null) {
            throw new \BadMethodCallException("Unknown recent activity event '" . $objectType . "'");
        }

        if ($userID === null && $username === null) {
            throw new \BadMethodCallException("Recent activity events of guests require a username");
        }

        $eventAction = new UserActivityEventAction([], 'create', [
            'data' => [
                'objectTypeID' => $objectTypeID,
                'objectID' => $objectID,
                'languageID' => $languageID,
                'userID' => $userID,
                'username' => $userID === null ? $username : null,
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
     * @param mixed[] $eventData
     */
    public function fireEvents(string $objectType, array $eventData): void
    {
        $objectTypeID = $this->getObjectTypeID($objectType);
        if ($objectTypeID === null) {
            throw new \BadMethodCallException("Unknown recent activity event '" . $objectType . "'");
        }

        $itemsPerLoop = 1000;
        $loopCount = \ceil(\count($eventData) / $itemsPerLoop);

        WCF::getDB()->beginTransaction();
        for ($i = 0; $i < $loopCount; $i++) {
            $batchEventData = \array_slice($eventData, $i * $itemsPerLoop, $itemsPerLoop);

            $parameters = [];
            foreach ($batchEventData as $data) {
                $userID = $data['userID'] ?? null;
                $username = $data['username'] ?? null;

                if ($userID === null && $username === null) {
                    throw new \BadMethodCallException("Recent activity events of guests require a username");
                }

                $parameters = \array_merge($parameters, [
                    $objectTypeID,
                    $data['objectID'],
                    $data['languageID'] ?? null,
                    $userID,
                    $userID !== null ? null : $username,
                    $data['time'] ?? \TIME_NOW,
                    \serialize($data['additionalData'] ?? []),
                ]);
            }

            $sql = "INSERT INTO wcf1_user_activity_event
                                (objectTypeID, objectID, languageID, userID, username, time, additionalData)
                    VALUES      (?, ?, ?, ?, ?, ?, ?)" . \str_repeat(', (?, ?, ?, ?, ?, ?, ?)', \count($batchEventData) - 1);
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($parameters);
        }
        WCF::getDB()->commitTransaction();
    }

    /**
     * Removes an activity event.
     */
    public function removeEvent(string $objectType, int $objectID, ?int $userID = null): void
    {
        $objectTypeID = $this->getObjectTypeID($objectType);
        if ($objectTypeID === null) {
            throw new \BadMethodCallException("Unknown recent activity event '" . $objectType . "'");
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
     * @param list<int> $objectIDs
     */
    public function removeEvents(string $objectType, array $objectIDs): void
    {
        if ($objectIDs === []) {
            return;
        }

        $objectTypeID = $this->getObjectTypeID($objectType);
        if ($objectTypeID === null) {
            throw new \BadMethodCallException("Unknown recent activity event '" . $objectType . "'");
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
     */
    public static function validateEvents(ViewableUserActivityEventList $eventList): void
    {
        $orphanedEventIDs = $eventList->validateEvents();
        if ($orphanedEventIDs === []) {
            return;
        }

        $sql = "DELETE FROM wcf1_user_activity_event
                WHERE       eventID = ?";
        $statement = WCF::getDB()->prepare($sql);

        foreach ($orphanedEventIDs as $eventID) {
            $statement->execute([$eventID]);
        }
    }
}
