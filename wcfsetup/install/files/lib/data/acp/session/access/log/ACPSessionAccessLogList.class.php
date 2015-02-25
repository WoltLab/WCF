<?php
namespace wcf\data\acp\session\access\log;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of access logs.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.session.access.log
 * @category	Community Framework
 */
class ACPSessionAccessLogList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\acp\session\access\log\ACPSessionAccessLog';
}
