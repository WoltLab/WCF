<?php

namespace wcf\system\worker;

use wcf\data\object\type\ObjectTypeCache;
use wcf\system\event\EventHandler;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Worker implementation for updating daily statistics.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class StatDailyRebuildDataWorker extends AbstractRebuildDataWorker
{
    /**
     * @inheritDoc
     */
    protected $limit = 30;

    /**
     * start timestamp
     * @var int
     */
    protected $startDate = 0;

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        // does nothing
    }

    /**
     * @inheritDoc
     */
    public function countObjects()
    {
        $this->getStartDate();

        $this->count = \ceil((TIME_NOW - $this->startDate) / 86400);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        EventHandler::getInstance()->fireAction($this, 'execute');

        if (!$this->loopCount) {
            // delete existing stat
            $sql = "DELETE FROM wcf1_stat_daily";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute();
        }

        // prepare insert statement
        $sql = "INSERT IGNORE INTO  wcf1_stat_daily
                                    (objectTypeID, date, counter, total)
                VALUES              (?, ?, ?, ?)";
        $statement = WCF::getDB()->prepare($sql);

        $this->getStartDate();
        $d = DateUtil::getDateTimeByTimestamp($this->startDate);
        $d->setTimezone(new \DateTimeZone(TIMEZONE));
        $d->setTime(0, 0);
        if ($this->loopCount) {
            $d->add(new \DateInterval('P' . ($this->loopCount * $this->limit) . 'D'));
        }
        for ($i = 0; $i < $this->limit; $i++) {
            if ($d->getTimestamp() > TIME_NOW) {
                break;
            }

            $objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.statDailyHandler');
            foreach ($objectTypes as $objectType) {
                $data = $objectType->getProcessor()->getData($d->getTimestamp());
                $statement->execute([$objectType->objectTypeID, $d->format('Y-m-d'), $data['counter'], $data['total']]);
            }

            $d->add(new \DateInterval('P1D'));
        }
    }

    /**
     * Determines the start timestamp.
     */
    protected function getStartDate()
    {
        if ($this->startDate) {
            return;
        }

        $sql = "SELECT  MIN(registrationDate)
                FROM    wcf1_user";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        $this->startDate = $statement->fetchSingleColumn();
    }
}
