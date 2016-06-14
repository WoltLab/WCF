<?php
namespace wcf\system\user;
use wcf\data\user\UserProfile;
use wcf\system\WCF;

/**
 * Provides a grouped list of users.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User
 */
class GroupedUserList implements \Countable, \Iterator {
	/**
	 * list of user profiles shared across all instances of GroupedUserList
	 * @var	UserProfile[]
	 */
	protected static $users = [];
	
	/**
	 * group name
	 * @var	string
	 */
	protected $groupName = '';
	
	/**
	 * current iterator index
	 * @var	integer
	 */
	protected $index = 0;
	
	/**
	 * message displayed if no users are in this group
	 * @var	string
	 */
	protected $noUsersMessage = '';
	
	/**
	 * list of user ids assigned for this group
	 * @var	integer[]
	 */
	protected $userIDs = [];
	
	/**
	 * Creates a new grouped list of users.
	 * 
	 * @param	string		$groupName
	 * @param	string		$noUsersMessage
	 */
	public function __construct($groupName = '', $noUsersMessage = '') {
		$this->groupName = $groupName;
		$this->noUsersMessage = $noUsersMessage;
	}
	
	/**
	 * Returns the group name.
	 * 
	 * @return	string
	 */
	public function getGroupName() {
		return $this->groupName;
	}
	
	/**
	 * Returns the message if no users are in this group.
	 * 
	 * @return	string
	 */
	public function getNoUsersMessage() {
		return ($this->noUsersMessage) ? WCF::getLanguage()->get($this->noUsersMessage) : '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function __toString() {
		return $this->getGroupName();
	}
	
	/**
	 * Adds a list of user ids to this group.
	 * 
	 * @param	integer[]		$userIDs
	 */
	public function addUserIDs(array $userIDs) {
		foreach ($userIDs as $userID) {
			// already added, ignore
			if (in_array($userID, $this->userIDs)) {
				continue;
			}
			
			$this->userIDs[] = $userID;
			
			// add entry to static cache
			self::$users[$userID] = null;
		}
	}
	
	/**
	 * Loads user profiles for outstanding user ids.
	 */
	public static function loadUsers() {
		$userIDs = [];
		foreach (self::$users as $userID => $user) {
			if ($user === null) {
				$userIDs[] = $userID;
			}
		}
		
		// load user profiles
		if (!empty($userIDs)) {
			$userProfiles = UserProfile::getUserProfiles($userIDs);
			foreach ($userProfiles as $userID => $userProfile) {
				self::$users[$userID] = $userProfile;
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function count() {
		return count($this->userIDs);
	}
	
	/**
	 * @inheritDoc
	 */
	public function current() {
		$userID = $this->userIDs[$this->index];
		return self::$users[$userID];
	}
	
	/**
	 * CAUTION: This methods does not return the current iterator index,
	 * rather than the object key which maps to that index.
	 * 
	 * @see	\Iterator::key()
	 */
	public function key() {
		return $this->userIDs[$this->index];
	}
	
	/**
	 * @inheritDoc
	 */
	public function next() {
		++$this->index;
	}
	
	/**
	 * @inheritDoc
	 */
	public function rewind() {
		$this->index = 0;
	}
	
	/**
	 * @inheritDoc
	 */
	public function valid() {
		return isset($this->userIDs[$this->index]);
	}
}
