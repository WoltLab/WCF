<?php
namespace wcf\system\user;
use wcf\data\user\UserProfile;
use wcf\system\WCF;

/**
 * Provides a grouped list of users.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user
 * @category	Community Framework
 */
class GroupedUserList implements \Countable, \Iterator {
	/**
	 * list of user profiles shared across all instances of GroupedUserList
	 * @var	array<\wcf\data\user\UserProfile>
	 */
	protected static $users = array();
	
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
	 * @var	array<integer>
	 */
	protected $userIDs = array();
	
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
	 * @see	\wcf\system\user\GroupedUserList::getGroupName()
	 */
	public function __toString() {
		return $this->getGroupName();
	}
	
	/**
	 * Adds a list of user ids to this group.
	 * 
	 * @param	array<integer>		$userIDs
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
		$userIDs = array();
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
	 * @see	\Countable::count()
	 */
	public function count() {
		return count($this->userIDs);
	}
	
	/**
	 * @see	\Iterator::current()
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
	 * @see	\Iterator::next()
	 */
	public function next() {
		++$this->index;
	}
	
	/**
	 * @see	\Iterator::rewind()
	 */
	public function rewind() {
		$this->index = 0;
	}
	
	/**
	 * @see	\Iterator::valid()
	 */
	public function valid() {
		return isset($this->userIDs[$this->index]);
	}
}
