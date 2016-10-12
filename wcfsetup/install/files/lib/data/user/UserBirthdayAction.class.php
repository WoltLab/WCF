<?php
namespace wcf\data\user;
use wcf\data\user\option\UserOption;
use wcf\data\IGroupedUserListAction;
use wcf\system\cache\builder\UserOptionCacheBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\user\UserBirthdayCache;
use wcf\system\WCF;

/**
 * Shows a list of user birthdays.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User
 */
class UserBirthdayAction extends UserProfileAction implements IGroupedUserListAction {
	/**
	 * @inheritDoc
	 */
	protected $allowGuestAccess = ['getGroupedUserList'];
	
	/**
	 * @inheritDoc
	 */
	public function validateGetGroupedUserList() {
		$this->readString('date');
		
		if (!preg_match('/\d{4}-\d{2}-\d{2}/', $this->parameters['date'])) {
			throw new UserInputException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getGroupedUserList() {
		$year = $month = $day = 0;
		$value = explode('-', $this->parameters['date']);
		if (isset($value[0])) $year = intval($value[0]);
		if (isset($value[1])) $month = intval($value[1]);
		if (isset($value[2])) $day = intval($value[2]);
		
		// get users
		$users = [];
		$userOptions = UserOptionCacheBuilder::getInstance()->getData([], 'options');
		if (isset($userOptions['birthday'])) {
			/** @var UserOption $birthdayUserOption */
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
		
		WCF::getTPL()->assign([
			'users' => $users,
			'year' => $year
		]);
		return [
			'pageCount' => 1,
			'template' => WCF::getTPL()->fetch('userBirthdayList')
		];
	}
}
