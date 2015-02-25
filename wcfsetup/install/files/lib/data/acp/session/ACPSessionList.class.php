<?php
namespace wcf\data\acp\session;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of ACP sessions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.session
 * @category	Community Framework
 */
class ACPSessionList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\acp\session\ACPSession';
}
