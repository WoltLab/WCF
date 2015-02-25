<?php
namespace wcf\data\session;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of sessions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.session
 * @category	Community Framework
 */
class SessionList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\session\Session';
}
