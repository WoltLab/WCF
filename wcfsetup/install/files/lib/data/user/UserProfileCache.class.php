<?php
namespace wcf\data\user;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\SingletonFactory;

/**
 * Caches user profile objects during runtime.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user
 * @category	Community Framework
 * @since	2.2
 * 
 * @todo	remove this class again
 */
class UserProfileCache extends SingletonFactory {
	/**
	 * @see	UserProfiltRuntimeCache::cacheObjectID()
	 */
	public function cacheUserID($userID) {
		UserProfileRuntimeCache::getInstance()->cacheObjectID($userID);
	}
	
	/**
	 * @see	UserProfiltRuntimeCache::cacheUserIDs()
	 */
	public function cacheUserIDs(array $userIDs) {
		UserProfileRuntimeCache::getInstance()->cacheObjectIDs($userIDs);
	}
	
	/**
	 * @see	UserProfiltRuntimeCache::getCachedObjects()
	 */
	public function getCachedUserProfiles() {
		return UserProfileRuntimeCache::getInstance()->getCachedObjects();
	}
	
	/**
	 * @see	UserProfiltRuntimeCache::getObject()
	 */
	public function getUserProfile($userID) {
		return UserProfileRuntimeCache::getInstance()->getObject($userID);
	}
	
	/**
	 * @see	UserProfiltRuntimeCache::getObjects()
	 */
	public function getUserProfiles(array $userIDs) {
		return UserProfileRuntimeCache::getInstance()->getObjects($userIDs);
	}
}
