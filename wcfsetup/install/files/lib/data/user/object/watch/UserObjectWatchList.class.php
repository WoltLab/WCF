<?php
namespace wcf\data\user\object\watch;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of watched objects.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.object.watch
 * @category	Community Framework
 */
class UserObjectWatchList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\user\object\watch\UserObjectWatch';
}
