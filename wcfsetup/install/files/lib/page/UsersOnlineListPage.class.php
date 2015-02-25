<?php
namespace wcf\page;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\breadcrumb\Breadcrumb;
use wcf\system\dashboard\DashboardHandler;
use wcf\system\request\LinkHandler;
use wcf\system\user\collapsible\content\UserCollapsibleContentHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Shows page which lists all users who are online.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	page
 * @category	Community Framework
 */
class UsersOnlineListPage extends SortablePage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.user.usersOnline';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('user.profile.canViewUsersOnlineList');
	
	/**
	 * @see	\wcf\page\AbstractPage::$enableTracking
	 */
	public $enableTracking = true;
	
	/**
	 * @see	\wcf\page\SortablePage::$defaultSortField
	 */
	public $defaultSortField = USERS_ONLINE_DEFAULT_SORT_FIELD;
	
	/**
	 * @see	\wcf\page\SortablePage::$defaultSortOrder
	 */
	public $defaultSortOrder = USERS_ONLINE_DEFAULT_SORT_ORDER;
	
	/**
	 * @see	\wcf\page\SortablePage::$validSortFields
	 */
	public $validSortFields = array('username', 'lastActivityTime', 'requestURI');
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$objectListClassName
	 */
	public $objectListClassName = 'wcf\data\user\online\UsersOnlineList';
	
	/**
	 * page locations
	 * @var	array
	 */
	public $locations = array();
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (WCF::getSession()->getPermission('admin.user.canViewIpAddress')) {
			$this->validSortFields[] = 'ipAddress';
			$this->validSortFields[] = 'userAgent';
		}
		
		if (!empty($_POST)) {
			HeaderUtil::redirect(LinkHandler::getInstance()->getLink('UsersOnlineList', array(), 'sortField=' . $this->sortField . '&sortOrder=' . $this->sortOrder));
			exit;
		}
	}
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::initObjectList()
	 */
	protected function initObjectList() {
		parent::initObjectList();
		$this->objectList->readStats();
		$this->objectList->checkRecord();
		
		if (!USERS_ONLINE_SHOW_ROBOTS) {
			$this->objectList->getConditionBuilder()->add('session.spiderID IS NULL');
		}
		if (!USERS_ONLINE_SHOW_GUESTS) {
			if (USERS_ONLINE_SHOW_ROBOTS) {
				$this->objectList->getConditionBuilder()->add('(session.userID IS NOT NULL OR session.spiderID IS NOT NULL)');
			}
			else {
				$this->objectList->getConditionBuilder()->add('session.userID IS NOT NULL');
			}
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		// add breadcrumbs
		if (MODULE_MEMBERS_LIST) WCF::getBreadcrumbs()->add(new Breadcrumb(WCF::getLanguage()->get('wcf.user.members'), LinkHandler::getInstance()->getLink('MembersList')));
		
		// load locations
		foreach (ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.user.online.location') as $objectType) {
			$this->locations[$objectType->controller] = $objectType;
		}
		
		// cache data
		foreach ($this->objectList as $userOnline) {
			if (isset($this->locations[$userOnline->controller]) && $this->locations[$userOnline->controller]->getProcessor()) {
				$this->locations[$userOnline->controller]->getProcessor()->cache($userOnline);
			}
		}
		
		// set locations
		foreach ($this->objectList as $userOnline) {
			if (isset($this->locations[$userOnline->controller])) {
				if ($this->locations[$userOnline->controller]->getProcessor()) {
					$userOnline->setLocation($this->locations[$userOnline->controller]->getProcessor()->get($userOnline, $this->locations[$userOnline->controller]->languagevariable));
				}
				else {
					$userOnline->setLocation(WCF::getLanguage()->get($this->locations[$userOnline->controller]->languagevariable));
				}
			}
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		DashboardHandler::getInstance()->loadBoxes('com.woltlab.wcf.user.MembersListPage', $this);
		
		WCF::getTPL()->assign(array(
			'sidebarCollapsed' => UserCollapsibleContentHandler::getInstance()->isCollapsed('com.woltlab.wcf.collapsibleSidebar', 'com.woltlab.wcf.user.MembersListPage'),
			'sidebarName' => 'com.woltlab.wcf.user.MembersListPage',
			'allowSpidersToIndexThisPage' => true
		));
	}
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::readObjects()
	 */
	protected function readObjects() {
		$this->objectList->sqlLimit = 0;
		if ($this->sqlOrderBy) $this->objectList->sqlOrderBy = ($this->sortField == 'lastActivityTime' ? 'session.' : '').$this->sqlOrderBy;
		$this->objectList->readObjects();
	}
}
