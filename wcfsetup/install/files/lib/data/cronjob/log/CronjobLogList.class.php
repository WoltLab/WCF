<?php
namespace wcf\data\cronjob\log;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of cronjob log entries.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Acp\Menu\Item
 *
 * @method	CronjobLog		current()
 * @method	CronjobLog[]		getObjects()
 * @method	CronjobLog|null		search($objectID)
 * @property	CronjobLog[]		$objects
 */
class CronjobLogList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = CronjobLog::class;
}
