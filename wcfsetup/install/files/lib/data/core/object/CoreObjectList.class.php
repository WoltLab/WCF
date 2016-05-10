<?php
namespace wcf\data\core\object;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of core objects.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.event
 * @category	Community Framework
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
