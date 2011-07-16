<?php
namespace wcf\data\cleanup\listener;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of cleanup listener.
 * 
 * @author 	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.cleanup.listener
 * @category 	Community Framework
 */
class CleanupListenerList extends DatabaseObjectList {
	/**
	 * @see	wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\cleanup\listener\CleanupListener';
}
