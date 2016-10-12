<?php
namespace wcf\page;
use wcf\system\menu\user\UserMenu;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;

/**
 * Shows a list with outstanding notifications of the active user.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Page
 */
class NotificationListPage extends MultipleLinkPage {
	/**
	 * @inheritDoc
	 */
	public $loginRequired = true;
	
	/**
	 * list of outstanding notifications
	 * @var	mixed[][]
	 */
	public $notifications = [];
	
	/**
	 * @inheritDoc
	 */
	public function countItems() {
		return UserNotificationHandler::getInstance()->countAllNotifications();
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {}
	
	/**
	 * @inheritDoc
	 */
	protected function readObjects() {}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		$this->notifications = UserNotificationHandler::getInstance()->getNotifications($this->sqlLimit, $this->sqlOffset, true);
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'notifications' => $this->notifications
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function show() {
		// set active tab
		UserMenu::getInstance()->setActiveMenuItem('wcf.user.menu.community.notification');
		
		parent::show();
	}
}
