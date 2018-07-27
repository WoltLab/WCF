<?php
namespace wcf\data\stat\daily;
use wcf\data\DatabaseObject;

/**
 * Represents a statistic entry.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Stat\Daily
 *
 * @property-read	integer		$statID		unique id of the daily statistic entry
 * @property-read	integer		$objectTypeID	id of the `com.woltlab.wcf.statDailyHandler` object type
 * @property-read	string		$date		date when the daily statistic entry has been created
 * @property-read	integer		$counter	daily statistic entry count for the last day
 * @property-read	integer		$total		cumulative daily statistic entry count up to the date
 */
class StatDaily extends DatabaseObject {}
