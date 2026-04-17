<?php

namespace wcf\system\visitTracker;

use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Handles object visit tracking.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class VisitTracker extends SingletonFactory
{
    /**
     * @deprecated 5.5 Use VisitTracker::LIFETIME instead.
     */
    const DEFAULT_LIFETIME = self::LIFETIME;

    /**
     * Objects older than this are considered visited.
     * @since 5.5
     */
    public const LIFETIME = 31 * 86400;

    /**
     * list of available object types
     * @var array<string, ObjectType>
     */
    protected $availableObjectTypes = [];

    /**
     * user visits
     * @var ?array<int, int>
     */
    protected $userVisits;

    #[\Override]
    protected function init()
    {
        // get available object types
        $this->availableObjectTypes = ObjectTypeCache::getInstance()
            ->getObjectTypes('com.woltlab.wcf.visitTracker.objectType');
    }

    /**
     * Returns the object type id of the given visit tracker object type.
     *
     * @return  int
     * @throws  SystemException
     */
    public function getObjectTypeID(string $objectType)
    {
        if (!isset($this->availableObjectTypes[$objectType])) {
            throw new SystemException("unknown object type '" . $objectType . "'");
        }

        return $this->availableObjectTypes[$objectType]->objectTypeID;
    }

    /**
     * Returns the last visit time for a whole object type.
     *
     * @return  int
     */
    public function getVisitTime(string $objectType)
    {
        if (!WCF::getUser()->userID) {
            return \TIME_NOW;
        }

        $objectTypeID = $this->getObjectTypeID($objectType);

        if ($this->userVisits === null) {
            $data = UserStorageHandler::getInstance()->getField('trackedUserVisits');

            // cache does not exist or is outdated
            if ($data === null) {
                $sql = "SELECT  objectTypeID, visitTime
                            FROM    wcf1_tracked_visit_type
                            WHERE   userID = ?";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([WCF::getUser()->userID]);
                $this->userVisits = $statement->fetchMap('objectTypeID', 'visitTime');

                // update storage data
                UserStorageHandler::getInstance()->update(
                    WCF::getUser()->userID,
                    'trackedUserVisits',
                    \serialize($this->userVisits)
                );
            } else {
                $this->userVisits = @\unserialize($data);
            }

            if (!$this->userVisits) {
                $this->userVisits = [];
            }
        }

        $minimum = \TIME_NOW - self::LIFETIME;

        // Mark everything before the registration date as read.
        $minimum = \max($minimum, WCF::getUser()->registrationDate);

        return \max($this->userVisits[$objectTypeID] ?? 0, $minimum);
    }

    /**
     * Returns the last visit time for a specific object.
     *
     * @return  int
     */
    public function getObjectVisitTime(string $objectType, int $objectID)
    {
        if (!WCF::getUser()->userID) {
            return \TIME_NOW;
        }

        $sql = "SELECT  visitTime
                FROM    wcf1_tracked_visit
                WHERE   objectTypeID = ?
                    AND objectID = ?
                    AND userID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->getObjectTypeID($objectType), $objectID, WCF::getUser()->userID]);
        $row = $statement->fetchArray();
        if ($row) {
            return $row['visitTime'];
        }

        return $this->getVisitTime($objectType);
    }

    /**
     * Deletes all tracked visits of a specific object type.
     *
     * @return void
     */
    public function deleteObjectVisits(string $objectType)
    {
        if (WCF::getUser()->userID) {
            $sql = "DELETE FROM wcf1_tracked_visit
                    WHERE       objectTypeID = ?
                            AND userID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$this->getObjectTypeID($objectType), WCF::getUser()->userID]);
        }
    }

    /**
     * Tracks an object visit for the users with the given ids.
     *
     * @param int[] $userIDs
     * @return void
     */
    public function trackObjectVisitByUserIDs(string $objectType, int $objectID, array $userIDs, int $time = \TIME_NOW)
    {
        // save visit
        $sql = "REPLACE INTO    wcf1_tracked_visit
                                (objectTypeID, objectID, userID, visitTime)
                VALUES          (?, ?, ?, ?)";
        $statement = WCF::getDB()->prepare($sql);
        $objectTypeID = $this->getObjectTypeID($objectType);
        WCF::getDB()->beginTransaction();

        foreach ($userIDs as $userID) {
            $statement->execute([$objectTypeID, $objectID, $userID, $time]);
        }

        WCF::getDB()->commitTransaction();
    }

    /**
     * Tracks an object visit.
     *
     * @return void
     */
    public function trackObjectVisit(string $objectType, int $objectID, int $time = \TIME_NOW)
    {
        if (!WCF::getUser()->userID) {
            return;
        }

        $sql = "REPLACE INTO    wcf1_tracked_visit
                                (objectTypeID, objectID, userID, visitTime)
                VALUES          (?, ?, ?, ?)";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->getObjectTypeID($objectType), $objectID, WCF::getUser()->userID, $time]);
    }

    /**
     * Tracks an object type visit.
     *
     * @return void
     */
    public function trackTypeVisit(string $objectType, int $time = \TIME_NOW)
    {
        if (!WCF::getUser()->userID) {
            return;
        }

        // save visit
        $sql = "REPLACE INTO    wcf1_tracked_visit_type
                                (objectTypeID, userID, visitTime)
                VALUES          (?, ?, ?)";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->getObjectTypeID($objectType), WCF::getUser()->userID, $time]);

        // delete obsolete object visits
        $sql = "DELETE FROM wcf1_tracked_visit
                WHERE       objectTypeID = ?
                        AND userID = ?
                        AND visitTime <= ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->getObjectTypeID($objectType), WCF::getUser()->userID, $time]);

        // reset storage
        UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'trackedUserVisits');
    }

    /**
     * Removes expired entries from the database.
     *
     * @since 6.0
     */
    public function prune(): void
    {
        $visitLifetime = 120 * 86400;
        // @phpstan-ignore function.alreadyNarrowedType, greater.alwaysTrue
        \assert($visitLifetime > self::LIFETIME);

        $sql = "DELETE FROM wcf1_tracked_visit
                WHERE       visitTime < ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            \TIME_NOW - $visitLifetime,
        ]);

        $sql = "DELETE FROM wcf1_tracked_visit_type
                WHERE       visitTime < ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            \TIME_NOW - $visitLifetime,
        ]);
    }
}
