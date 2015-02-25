<?php
namespace wcf\page;
use wcf\system\menu\user\UserMenu;
use wcf\system\WCF;

/**
 * Shows a list with all users the active user ignores.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	page
 * @category	Community Framework
 */
class IgnoredUsersPage extends MultipleLinkPage {
	/**
	 * @see	\wcf\page\AbstractPage::$loginRequired
	 */
	public $loginRequired = true;
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$objectListClassName
	 */
	public $objectListClassName = 'wcf\data\user\ignore\ViewableUserIgnoreList';
	
	/**
	 * @see	\wcf\data\DatabaseObjectList::$sqlOrderBy
	 */
	public $sqlOrderBy = 'user_ignore.time DESC';
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::readData()
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->getConditionBuilder()->add("user_ignore.userID = ?", array(WCF::getUser()->userID));
	}
	
	/**
	 * @see	\wcf\page\Page::show()
	 */
	public function show() {
		// set active tab
		UserMenu::getInstance()->setActiveMenuItem('wcf.user.menu.community.ignoredUsers');
		
		parent::show();
	}
}
