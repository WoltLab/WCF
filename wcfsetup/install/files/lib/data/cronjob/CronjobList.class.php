<?php
namespace wcf\data\cronjob;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of cronjobs.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.cronjob
 * @category	Community Framework
 */
class CronjobList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\cronjob\Cronjob';
}
