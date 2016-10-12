<?php
namespace wcf\system\acl\simple;
use wcf\data\user\User;
use wcf\system\cache\builder\SimpleAclCacheBuilder;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Simplified ACL handlers that stores access data for objects requiring
 * just a simple yes/no instead of a set of different permissions.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Acl\Simple
 */
class SimpleAclResolver extends SingletonFactory {
	/**
	 * cached permissions per object type
	 * @var array
	 */
	protected $cache = [];
	
	/**
	 * Returns true if there are no ACL settings, the user is allowed or
	 * one of its group is allowed.
	 * 
	 * @param       string          $objectType     object type name
	 * @param       integer         $objectID       object id
	 * @param       User|null       $user           user object, if `null` uses current user
	 * @return      boolean         false if user is not allowed
	 */
	public function canAccess($objectType, $objectID, User $user = null) {
		if ($user === null) $user = WCF::getUser();
		
		$this->loadCache($objectType);
		
		// allow all
		if (!isset($this->cache[$objectType][$objectID])) {
			return true;
		}
		
		if ($user->userID) {
			// user is explicitly allowed
			if (in_array($user->userID, $this->cache[$objectType][$objectID]['user'])) {
				return true;
			}
		}
		
		// check for user groups
		$groupIDs = $user->getGroupIDs();
		foreach ($groupIDs as $groupID) {
			// group is explicitly allowed
			if (in_array($groupID, $this->cache[$objectType][$objectID]['group'])) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Resets the cache for provided object type.
	 * 
	 * @param       string          $objectType     object type name
	 */
	public function resetCache($objectType) {
		SimpleAclCacheBuilder::getInstance()->reset(['objectType' => $objectType]);
	}
	
	/**
	 * Attempts to load the cache for provided object type.
	 * 
	 * @param       string          $objectType     object type name
	 */
	protected function loadCache($objectType) {
		if (!isset($this->cache[$objectType])) {
			$this->cache[$objectType] = SimpleAclCacheBuilder::getInstance()->getData(['objectType' => $objectType]);
		}
	}
}
