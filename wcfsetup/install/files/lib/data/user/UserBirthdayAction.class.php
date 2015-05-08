<?php
namespace wcf\data\user;
use wcf\data\user\UserProfileAction;
use wcf\data\user\UserProfileList;
use wcf\data\IGroupedUserListAction;
use wcf\system\cache\builder\UserOptionCacheBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\user\UserBirthdayCache;
use wcf\system\WCF;

/**
 * Shows a list of user birthdays.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user
 * @category	Community Framework
 */
class UserBirthdayAction extends UserProfileAction implements IGroupedUserListAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$allowGuestAccess
	 */
	protected $allowGuestAccess = array('getGroupedUserList');
	
	/**
	 * @see	\wcf\data\IGroupedUserListAction::validateGetGroupedUserList()
	 */
	public function validateGetGroupedUserList() {
		$this->readString('date');
		
		if (!preg_match('/\d{4}-\d{2}-\d{2}/', $this->parameters['date'])) {
			throw new UserInputException();
		}
	}
	
	/**
	 * @see	\wcf\data\IGroupedUserListAction::getGroupedUserList()
	 */
	public function getGroupedUserList() {
		$year = $month = $day = 0;
		$value = explode('-', $this->parameters['date']);
		if (isset($value[0])) $year = intval($value[0]);
		if (isset($value[1])) $month = intval($value[1]);
		if (isset($value[2])) $day = intval($value[2]);
		
		// get users
		$users = array();
		$userOptions = UserOptionCacheBuilder::getInstance()->getData(array(), 'options');
		if (isset($userOptions['birthday'])) {
			$birthdayUserOption = $userOptions['birthday'];
			
			$userIDs = UserBirthdayCache::getInstance()->getBirthdays($month, $day);
			$userList = new UserProfileList();
			$userList->setObjectIDs($userIDs);
			$userList->readObjects();
				
			foreach ($userList->getObjects() as $user) {
				$birthdayUserOption->setUser($user->getDecoratedObject());
					
				if (!$user->isProtected() && $birthdayUserOption->isVisible() && $user->getAge($year) >= 0) {
					$users[] = $user;
				}
			}
		}
		
		WCF::getTPL()->assign(array(
			'users' => $users,
			'year' => $year
		));
		return array(
			'pageCount' => 1,
			'template' => WCF::getTPL()->fetch('userBirthdayList')
		);
	}
}
