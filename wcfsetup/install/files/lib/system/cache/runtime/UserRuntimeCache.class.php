<?php
namespace wcf\system\cache\runtime;
use wcf\data\user\User;
use wcf\data\user\UserList;

/**
 * Runtime cache implementation for users.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Runtime
 * @since	3.0
 * 
 * @method	User[]		getCachedObjects()
 * @method	User		getObject($objectID)
 * @method	User[]		getObjects(array $objectIDs)
 */
class UserRuntimeCache extends AbstractRuntimeCache {
	/**
	 * @inheritDoc
	 */
	protected $listClassName = UserList::class;
}
