<?php
namespace wcf\data\stat\daily;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of statistic entries.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Stat\Daily
 *
 * @method	StatDaily		current()
 * @method	StatDaily[]		getObjects()
 * @method	StatDaily|null		search($objectID)
 * @property	StatDaily[]		$objects
 */
class StatDailyList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = StatDaily::class;
}
