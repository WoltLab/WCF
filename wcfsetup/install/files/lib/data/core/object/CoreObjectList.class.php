<?php
namespace wcf\data\core\object;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of core objects.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Event
 *
 * @method	CoreObject		current()
 * @method	CoreObject[]		getObjects()
 * @method	CoreObject|null		search($objectID)
 * @property	CoreObject[]		$objects
 */
class CoreObjectList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = CoreObject::class;
}
