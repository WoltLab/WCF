<?php

namespace wcf\data\stat\daily;

use wcf\data\DatabaseObject;

/**
 * Represents a statistic entry.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\Stat\Daily
 *
 * @property-read   int $statID     unique id of the daily statistic entry
 * @property-read   int $objectTypeID   id of the `com.woltlab.wcf.statDailyHandler` object type
 * @property-read   string $date       date when the daily statistic entry has been created
 * @property-read   int $counter    daily statistic entry count for the last day
 * @property-read   int $total      cumulative daily statistic entry count up to the date
 */
class StatDaily extends DatabaseObject
{
}
