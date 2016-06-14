<?php
namespace wcf\data\user;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\SingletonFactory;

/**
 * Caches user profile objects during runtime.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User
 * @since	3.0
 * 
 * @todo	remove this class again
 */
class UserProfileCache extends SingletonFactory {
	/**
	 * @inheritDoc
	 */
	public function cacheUserID($userID) {
		UserProfileRuntimeCache::getInstance()->cacheObjectID($userID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function cacheUserIDs(array $userIDs) {
		UserProfileRuntimeCache::getInstance()->cacheObjectIDs($userIDs);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getCachedUserProfiles() {
		return UserProfileRuntimeCache::getInstance()->getCachedObjects();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getUserProfile($userID) {
		return UserProfileRuntimeCache::getInstance()->getObject($userID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getUserProfiles(array $userIDs) {
		return UserProfileRuntimeCache::getInstance()->getObjects($userIDs);
	}
}
