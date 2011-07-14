<?php
namespace wcf\data\session\data;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of session data.
 * 
 * @author 	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.session.data
 * @category 	Community Framework
 */
class SessionDataList extends DatabaseObjectList {
	/**
	 * @see	DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\session\data\SessionData';
}
