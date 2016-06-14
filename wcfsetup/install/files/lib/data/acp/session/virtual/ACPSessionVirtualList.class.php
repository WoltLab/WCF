<?php
namespace wcf\data\acp\session\virtual;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of virtual sessions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Acp\Session\Virtual
 *
 * @method	ACPSessionVirtual		current()
 * @method	ACPSessionVirtual[]		getObjects()
 * @method	ACPSessionVirtual|null		search($objectID)
 * @property	ACPSessionVirtual[]		$objects
 */
class ACPSessionVirtualList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = ACPSessionVirtual::class;
}
