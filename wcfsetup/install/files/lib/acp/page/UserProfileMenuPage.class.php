<?php
namespace wcf\acp\page;
use wcf\data\user\profile\menu\item\UserProfileMenuItemList;
use wcf\page\AbstractPage;
use wcf\system\WCF;

/**
 * Provides sorting capabilities for the user profile menu.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 */
class UserProfileMenuPage extends AbstractPage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.user.profileMenu';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.user.canManageUserOption'];
	
	/**
	 * user profile menu item list object
	 * @var UserProfileMenuItemList
	 */
	public $userProfileMenuItemList;
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		$this->userProfileMenuItemList = new UserProfileMenuItemList();
		$this->userProfileMenuItemList->sqlOrderBy = "showOrder";
		$this->userProfileMenuItemList->readObjects();
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'userProfileMenuItemList' => $this->userProfileMenuItemList
		]);
	}
}
