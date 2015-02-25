<?php
namespace wcf\data\cronjob\log;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of cronjob log entries.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.menu.item
 * @category	Community Framework
 */
class CronjobLogList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\cronjob\log\CronjobLog';
}
