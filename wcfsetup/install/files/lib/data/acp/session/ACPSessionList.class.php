<?php
namespace wcf\data\acp\session;

/**
 * Represents a list of ACP sessions.
 * 
 * @author 	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.session
 * @category 	Community Framework
 */
class ACPSessionList extends DatabaseObjectList {
	/**
	 * @see	DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\acp\session\ACPSession';
}
