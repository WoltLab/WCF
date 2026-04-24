<?php

namespace wcf\data;

use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Trait for dbo collections with visit tracking.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
trait TCollectionVisitTimes
{
    /**
     * @var array<int, int>
     */
    private array $visitTimes;

    public function getVisitTime(DatabaseObject $object): int
    {
        $this->loadVisitTimes();

        return $this->visitTimes[$object->getObjectID()] ?? 0;
    }

    private function loadVisitTimes(): void
    {
        if (isset($this->visitTimes)) {
            return;
        }

        $this->visitTimes = [];

        if (WCF::getUser()->isGuest()) {
            return;
        }

        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add("objectTypeID = ?", [
            VisitTracker::getInstance()->getObjectTypeID($this->getVisitTrackerObjectType())
        ]);
        $conditionBuilder->add("objectID IN (?)", [$this->getObjectIDs()]);
        $conditionBuilder->add("userID = ?", [WCF::getUser()->userID]);
        $sql = "SELECT  objectID, visitTime
                FROM    wcf1_tracked_visit
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());
        $this->visitTimes = $statement->fetchMap('objectID', 'visitTime');
    }

    protected abstract function getVisitTrackerObjectType(): string;
}
