<?php

namespace wcf\system\visitTracker;

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
     * @var array
     */
    protected $availableObjectTypes = [];

    /**
     * user visits
     * @var array
     */
    protected $userVisits;

    /**
     * @inheritDoc
     */
    protected function init()
    {
        // get available object types
        $this->availableObjectTypes = ObjectTypeCache::getInstance()
            ->getObjectTypes('com.woltlab.wcf.visitTracker.objectType');
    }

    /**
     * Returns the object type id of the given visit tracker object type.
     *
     * @param string $objectType
     * @return  int
     * @throws  SystemException
     */
    public function getObjectTypeID($objectType)
    {
        if (!isset($this->availableObjectTypes[$objectType])) {
            throw new SystemException("unknown object type '" . $objectType . "'");
        }

        return $this->availableObjectTypes[$objectType]->objectTypeID;
    }

    /**
     * Returns the last visit time for a whole object type.
     *
     * @param string $objectType
     * @return  int
     */
    public function getVisitTime($objectType)
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

        $minimum = TIME_NOW - self::LIFETIME;

        // Mark everything before the registration date as read.
        $minimum = \max($minimum, WCF::getUser()->registrationDate);

        return \max($this->userVisits[$objectTypeID] ?? 0, $minimum);
    }

    /**
     * Returns the last visit time for a specific object.
     *
     * @param string $objectType
     * @param int $objectID
     * @return  int
     */
    public function getObjectVisitTime($objectType, $objectID)
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
     * @param string $objectType
     */
    public function deleteObjectVisits($objectType)
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
     * @param string $objectType
     * @param int $objectID
     * @param int[] $userIDs
     * @param int $time
     */
    public function trackObjectVisitByUserIDs($objectType, $objectID, array $userIDs, $time = TIME_NOW)
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
     * @param string $objectType
     * @param int $objectID
     * @param int $time
     */
    public function trackObjectVisit($objectType, $objectID, $time = TIME_NOW)
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
     * @param string $objectType
     * @param int $time
     */
    public function trackTypeVisit($objectType, $time = TIME_NOW)
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
        \assert($visitLifetime > self::LIFETIME);

        $sql = "DELETE FROM wcf1_tracked_visit
                WHERE       visitTime < ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            TIME_NOW - $visitLifetime,
        ]);

        $sql = "DELETE FROM wcf1_tracked_visit_type
                WHERE       visitTime < ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            TIME_NOW - $visitLifetime,
        ]);
    }
}
