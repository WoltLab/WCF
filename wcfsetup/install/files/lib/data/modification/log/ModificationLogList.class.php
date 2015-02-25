<?php
namespace wcf\data\modification\log;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of modification logs.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.modification.log
 * @category	Community Framework
 */
class ModificationLogList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\modification\log\ModificationLog';
}
