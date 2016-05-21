<?php
namespace wcf\system\user\online\location;
use wcf\data\user\online\UserOnline;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\WCF;

/**
 * Implementation of IUserOnlineLocation for the user profile location.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.online.location
 * @category	Community Framework
 * @deprecated	since 2.2
 */
class UserLocation implements IUserOnlineLocation {
	/**
	 * user ids
	 * @var	integer[]
	 */
	protected $userIDs = [];
	
	/**
	 * list of users
	 * @var	User[]
	 */
	protected $users = null;
	
	/**
	 * @inheritDoc
	 */
	public function cache(UserOnline $user) {
		if ($user->objectID) $this->userIDs[] = $user->objectID;
	}
	
	/**
	 * @inheritDoc
	 */
	public function get(UserOnline $user, $languageVariable = '') {
		if ($this->users === null) {
			$this->readUsers();
		}
		
		if (!isset($this->users[$user->objectID])) {
			return '';
		}
		
		return WCF::getLanguage()->getDynamicVariable($languageVariable, ['user' => $this->users[$user->objectID]]);
	}
	
	/**
	 * Loads the users.
	 */
	protected function readUsers() {
		$this->users = [];
		
		if (empty($this->userIDs)) return;
		$this->userIDs = array_unique($this->userIDs);
		
		$userList = new UserList();
		$userList->setObjectIDs($this->userIDs);
		$userList->readObjects();
		$this->users = $userList->getObjects();
	}
}
