<?php
namespace wcf\data\acp\session\data;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of ACP session data.
 * 
 * @author 	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.session.data
 * @category 	Community Framework
 */
class ACPSessionDataList extends DatabaseObjectList {
	/**
	 * @see	wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\acp\session\data\ACPSessionData';
}
