<?php

namespace wcf\data\cronjob;

use Cron\CronExpression;
use wcf\data\DatabaseObject;
use wcf\data\package\Package;
use wcf\data\package\PackageCache;
use wcf\data\TDatabaseObjectOptions;
use wcf\system\WCF;

/**
 * Represents a cronjob.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $cronjobID      unique id of the cronjob
 * @property-read   string $className      PHP class name implementing `wcf\system\cronjob\ICronjob`
 * @property-read   int $packageID      id of the package which delivers the cronjob or the id of the active application during creation in the ACP
 * @property-read   string $cronjobName        name and textual identifier of the cronjob
 * @property-read   string $description        description of the cronjob or name of language item which contains the description
 * @property-read   string $startMinute        minutes in the hour at which the cronjob is executed, wildcard `*` (any minute) or a rule using wildcard `*`
 * @property-read   string $startHour      hour in the day at which the cronjob is executed, wildcard `*` (any hour) or a rule using wildcard `*`
 * @property-read   string $startDom       day of the month at which the cronjob is executed, wildcard `*` (any day) or a rule using wildcard `*`
 * @property-read   string $startMonth     month in the year in which the cronjob is executed, wildcard `*` (any month) or a rule using wildcard `*`
 * @property-read   string $startDow       day in the week at which the cronjob is executed, wildcard `*` (any day) or a rule using wildcard `*`
 * @property-read   int $lastExec       timestamp at which the cronjob has been executed the last time
 * @property-read   int $nextExec       timestamp at which the cronjob will be executed next
 * @property-read   int $afterNextExec      timestamp at which the cronjob will be executed after next
 * @property-read   int $isDisabled     is `1` if the cronjob is disabled and thus not executed, otherwise `0`
 * @property-read   int $canBeEdited        is `1` if the cronjob can be edited in the ACP, otherwise `0`
 * @property-read   int $canBeDisabled      is `1` if the cronjob can be disabled in the ACP so that it will not be executed, otherwise `0`
 * @property-read   int $state          current state of the cronjob (see `Cronjob::READY`, `Cronjob::PENDING`, `Cronjob::EXECUTING` and `Cronjob::MAX_FAIL_COUNT`)
 * @property-read   int $failCount      number of times the cronjob execution failed consecutively
 * @property-read   string $options        comma separated list of options of which at least one needs to be enabled for the cronjob to be executed
 */
class Cronjob extends DatabaseObject
{
    use TDatabaseObjectOptions;

    /**
     * indicates that cronjob is available for execution
     */
    const READY = 0;

    /**
     * indicates that cronjob is currently processed, preventing multiple execution
     */
    const PENDING = 1;

    /**
     * indicates that cronjob is executed at the moment
     */
    const EXECUTING = 2;

    /**
     * maximum number of allowed fails
     */
    const MAX_FAIL_COUNT = 3;

    /**
     * Returns timestamp of next execution.
     *
     * @param int $timeBase
     */
    public function getNextExec($timeBase = null): int
    {
        if ($timeBase === null) {
            if ($this->lastExec) {
                $timeBase = $this->lastExec + 120;
                if ($timeBase < TIME_NOW) {
                    $timeBase = TIME_NOW + 120;
                }
            } else {
                // first time setup
                $timeBase = TIME_NOW;
            }
        }

        $dateTime = (new \DateTimeImmutable("@{$timeBase}"))
            // The TZ parameter in the constructor is ignored for timestamps.
            ->setTimezone(new \DateTimeZone(TIMEZONE));

        return $this->getExpression()->getNextRunDate($dateTime)->getTimestamp();
    }

    /**
     * @since 6.0
     */
    public function getExpression(): CronExpression
    {
        return new CronExpression(\sprintf(
            '%s %s %s %s %s',
            $this->startMinute,
            $this->startHour,
            $this->startDom,
            $this->startMonth,
            $this->startDow,
        ));
    }

    /**
     * Returns true if current user may edit this cronjob.
     *
     * @return  bool
     */
    public function isEditable()
    {
        return $this->canBeEdited;
    }

    /**
     * Returns true if current user may delete this cronjob.
     *
     * @return  bool
     */
    public function isDeletable()
    {
        return $this->canBeEdited && $this->canBeDisabled;
    }

    /**
     * Returns true if current user may enable or disable this cronjob.
     *
     * @return  bool
     */
    public function canBeDisabled()
    {
        return $this->canBeDisabled;
    }

    /**
     * Returns the cronjob description in the active user's language.
     *
     * @return  string
     * @since   5.2
     */
    public function getDescription()
    {
        return WCF::getLanguage()->get($this->description);
    }

    /**
     * @since   6.1
     */
    public function getPackage(): Package
    {
        return PackageCache::getInstance()->getPackage($this->packageID);
    }
}
