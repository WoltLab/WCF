<?php
namespace wcf\page;
use wcf\data\user\ignore\ViewableUserIgnoreList;
use wcf\system\menu\user\UserMenu;
use wcf\system\WCF;

/**
 * Shows a list with all users the active user ignores.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Page
 * 
 * @property	ViewableUserIgnoreList	$objectList
 */
class IgnoredUsersPage extends MultipleLinkPage {
	/**
	 * @inheritDoc
	 */
	public $loginRequired = true;
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = ViewableUserIgnoreList::class;
	
	/**
	 * @inheritDoc
	 */
	public $sqlOrderBy = 'user_ignore.time DESC';
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->getConditionBuilder()->add("user_ignore.userID = ?", [WCF::getUser()->userID]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function show() {
		// set active tab
		UserMenu::getInstance()->setActiveMenuItem('wcf.user.menu.community.ignoredUsers');
		
		parent::show();
	}
}
