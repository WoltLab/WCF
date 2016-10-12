<?php
namespace wcf\data\user\object\watch;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of watched objects.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Object\Watch
 *
 * @method	UserObjectWatch		current()
 * @method	UserObjectWatch[]	getObjects()
 * @method	UserObjectWatch|null	search($objectID)
 * @property	UserObjectWatch[]	$objects
 */
class UserObjectWatchList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = UserObjectWatch::class;
}
