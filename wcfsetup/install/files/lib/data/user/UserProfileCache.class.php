<?php
namespace wcf\data\user;
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
 */
class UserProfileCache extends SingletonFactory {
	/**
	 * cached user ids whose profiles will be loaded during the next request
	 * @var	array<integer>
	 */
	protected $userIDs = array();
	
	/**
	 * locally cached user profiles
	 * @var	array<\wcf\data\user\UserProfile>
	 */
	protected $userProfiles = array();
	
	/**
	 * Caches the given user id.
	 * 
	 * @param	integer		$userID
	 */
	public function cacheUserID($userID) {
		$this->userIDs[] = $userID;
	}
	
	/**
	 * Caches the given user ids.
	 * 
	 * @param	array<integer>		$userID
	 */
	public function cacheUserIDs(array $userIDs) {
		$this->userIDs = array_merge($this->userIDs, $userIDs);
	}
	
	/**
	 * Returns all currently cached user profile objects.
	 * 
	 * @return	array<\wcf\data\user\UserProfile>
	 */
	public function getCachedUserProfiles() {
		return $this->userProfiles;
	}
	
	/**
	 * Returns the user profile of the user with the given user id. If no such
	 * user profile exists, null is returned.
	 * 
	 * @param	integer		$userID
	 * @return	\wcf\data\user\UserProfile
	 */
	public function getUserProfile($userID) {
		if (array_key_exists($userID, $this->userProfiles)) {
			return $this->userProfiles[$userID];
		}
		
		return $this->getUserProfiles(array($userID))[$userID];
	}
	
	/**
	 * Returns the user profiles of the users with the given user ids. For ids
	 * without a user profile, null is returned.
	 * 
	 * @param	array<integer>		$userIDs
	 * @return	array<\wcf\data\user\UserProfile>
	 */
	public function getUserProfiles(array $userIDs) {
		$userProfiles = array();
		foreach ($userIDs as $key => $userID) {
			if (array_key_exists($userID, $this->userProfiles)) {
				$userProfiles[$userID] = $this->userProfiles[$userID];
				
				unset($userIDs[$key]);
			}
		}
		
		if (empty($userIDs)) {
			return $userProfiles;
		}
		
		$this->userIDs = array_unique(array_merge($this->userIDs, $userIDs));
		
		$userProfileList = new UserProfileList();
		$userProfileList->setObjectIDs($this->userIDs);
		$userProfileList->readObjects();
		$readUserProfiles = $userProfileList->getObjects();
		
		foreach ($this->userIDs as $userID) {
			if (!isset($readUserProfiles[$userID])) {
				$this->userProfiles[$userID] = null;
			}
			else {
				$this->userProfiles[$userID] = $readUserProfiles[$userID];
			}
		}
		
		$this->userIDs = array();
		
		foreach ($userIDs as $userID) {
			$userProfiles[$userID] = $this->userProfiles[$userID];
		}
		
		return $userProfiles;
	}
}
