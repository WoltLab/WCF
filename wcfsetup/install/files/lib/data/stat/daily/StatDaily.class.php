<?php
namespace wcf\data\stat\daily;
use wcf\data\DatabaseObject;

/**
 * Represents a statistic entry.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.stat.daily
 * @category	Community Framework
 *
 * @property-read	integer		$statID
 * @property-read	integer		$objectTypeID
 * @property-read	string		$date
 * @property-read	integer		$counter
 * @property-read	integer		$total
 */
class StatDaily extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'stat_daily';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'statID';
}
