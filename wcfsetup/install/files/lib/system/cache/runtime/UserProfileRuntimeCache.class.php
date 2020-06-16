<?php
namespace wcf\system\cache\runtime;
use wcf\data\user\UserProfile;
use wcf\data\user\UserProfileList;

/**
 * Runtime cache implementation for user profiles.
 *
 * @author	Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Runtime
 * @since	3.0
 * 
 * @method	UserProfile[]	getCachedObjects()
 * @method	UserProfile	getObject($objectID)
 * @method	UserProfile[]	getObjects(array $objectIDs)
 */
class UserProfileRuntimeCache extends AbstractRuntimeCache {
	/**
	 * @inheritDoc
	 */
	protected $listClassName = UserProfileList::class;
	
	/**
	 * Adds a user profile to the cache. This is an internal method that should
	 * not be used on a regular basis.
	 * 
	 * @param       UserProfile     $profile
	 * @since       3.1
	 */
	public function addUserProfile(UserProfile $profile) {
		$objectID = $profile->getObjectID();
		
		if (!isset($this->objects[$objectID])) {
			$this->objects[$objectID] = $profile;
		}
	}
}
