<?php
namespace wcf\system\box;
use wcf\data\user\online\UsersOnlineList;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Box controller for a list of registered users who are currently online.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.box
 * @category	Community Framework
 * @since	2.2
 */
class UserOnlineListBoxController extends AbstractDatabaseObjectListBoxController {
	/**
	 * @inheritDoc
	 */
	protected $supportedPositions = ['footerBoxes', 'sidebarLeft', 'sidebarRight'];
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return LinkHandler::getInstance()->getLink('UsersOnlineList');
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getObjectList() {
		$objectList = new UsersOnlineList();
		$objectList->readStats();
		$objectList->checkRecord();
		$objectList->getConditionBuilder()->add('session.userID IS NOT NULL');
		
		return $objectList;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getTemplate() {
		$templateName = 'boxUsersOnlineSidebar';
		if ($this->getBox()->position == 'footerBoxes') {
			$templateName = 'boxUsersOnline';
		}
		
		return WCF::getTPL()->fetch($templateName, 'wcf', ['usersOnlineList' => $this->objectList]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasContent() {
		if (!MODULE_USERS_ONLINE || WCF::getSession()->getPermission('user.profile.canViewUsersOnlineList')) {
			return false;
		}
		
		return parent::hasContent();
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasLink() {
		return true;
	}
}
