<?php
namespace wcf\system\user\online\location;
use wcf\data\user\online\UserOnline;
use wcf\data\user\UserList;
use wcf\system\WCF;

/**
 * Implementation of IUserOnlineLocation for the user profile location.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.online.location
 * @category	Community Framework
 */
class UserLocation implements IUserOnlineLocation {
	/**
	 * user ids
	 * @var	array<integer>
	 */
	protected $userIDs = array();
	
	/**
	 * list of users
	 * @var	array<\wcf\data\user\User>
	 */
	protected $users = null;
	
	/**
	 * @see	\wcf\system\user\online\location\IUserOnlineLocation::cache()
	 */
	public function cache(UserOnline $user) {
		if ($user->objectID) $this->userIDs[] = $user->objectID;
	}
	
	/**
	 * @see	\wcf\system\user\online\location\IUserOnlineLocation::get()
	 */
	public function get(UserOnline $user, $languageVariable = '') {
		if ($this->users === null) {
			$this->readUsers();
		}
		
		if (!isset($this->users[$user->objectID])) {
			return '';
		}
		
		return WCF::getLanguage()->getDynamicVariable($languageVariable, array('user' => $this->users[$user->objectID]));
	}
	
	/**
	 * Loads the users.
	 */
	protected function readUsers() {
		$this->users = array();
		
		if (empty($this->userIDs)) return;
		$this->userIDs = array_unique($this->userIDs);
		
		$userList = new UserList();
		$userList->getConditionBuilder()->add('user_table.userID IN (?)', array($this->userIDs));
		$userList->readObjects();
		$this->users = $userList->getObjects();
	}
}
