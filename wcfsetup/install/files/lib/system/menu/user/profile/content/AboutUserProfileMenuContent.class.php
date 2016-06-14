<?php
namespace wcf\system\menu\user\profile\content;
use wcf\data\user\User;
use wcf\system\option\user\UserOptionHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles user profile information content.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Menu\User\Profile\Content
 */
class AboutUserProfileMenuContent extends SingletonFactory implements IUserProfileMenuContent {
	/**
	 * user option handler object
	 * @var	\wcf\system\option\user\UserOptionHandler
	 */
	public $optionHandler = null;
	
	/**
	 * @inheritDoc
	 */
	public function getContent($userID) {
		if ($this->optionHandler === null) {
			$this->optionHandler = new UserOptionHandler(false, '', 'profile');
			$this->optionHandler->enableEditMode(false);
			$this->optionHandler->showEmptyOptions(false);
		}
		
		$user = new User($userID);
		$this->optionHandler->setUser($user);
		
		WCF::getTPL()->assign([
			'options' => $this->optionHandler->getOptionTree(),
			'userID' => $user->userID
		]);
		
		return WCF::getTPL()->fetch('userProfileAbout');
	}
	
	/**
	 * @inheritDoc
	 */
	public function isVisible($userID) {
		return true;
	}
}
