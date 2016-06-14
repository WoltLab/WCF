<?php
namespace wcf\data\acp\session\access\log;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of access logs.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Acp\Session\Access\Log
 *
 * @method	ACPSessionAccessLog		current()
 * @method	ACPSessionAccessLog[]		getObjects()
 * @method	ACPSessionAccessLog|null	search($objectID)
 * @property	ACPSessionAccessLog[]		$objects
 */
class ACPSessionAccessLogList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = ACPSessionAccessLog::class;
}
