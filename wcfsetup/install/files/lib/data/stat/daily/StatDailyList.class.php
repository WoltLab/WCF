<?php
namespace wcf\data\stat\daily;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of statistic entries.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.stat.daily
 * @category	Community Framework
 */
class StatDailyList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\stat\daily\StatDaily';
}
