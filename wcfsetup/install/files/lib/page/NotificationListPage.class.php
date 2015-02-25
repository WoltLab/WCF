<?php
namespace wcf\page;
use wcf\system\menu\user\UserMenu;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;

/**
 * Shows a list with outstanding notifications of the active user.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	page
 * @category	Community Framework
 */
class NotificationListPage extends MultipleLinkPage {
	/**
	 * @see	\wcf\page\AbstractPage::$loginRequired
	 */
	public $loginRequired = true;
	
	/**
	 * list of outstanding notifications
	 * @var	array<array>
	 */
	public $notifications = array();
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::countItems()
	 */
	public function countItems() {
		return UserNotificationHandler::getInstance()->countAllNotifications();
	}
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::initObjectList()
	 */
	protected function initObjectList() {}
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::readObjects()
	 */
	protected function readObjects() {}
	
	/**
	 * @see	\wcf\page\AbstractPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->notifications = UserNotificationHandler::getInstance()->getNotifications($this->sqlLimit, $this->sqlOffset, true);
	}
	
	/**
	 * @see	\wcf\page\AbstractPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'notifications' => $this->notifications
		));
	}
	
	/**
	 * @see	\wcf\page\Page::show()
	 */
	public function show() {
		// set active tab
		UserMenu::getInstance()->setActiveMenuItem('wcf.user.menu.community.notification');
		
		parent::show();
	}
}
