<?php
namespace wcf\page;
use wcf\data\search\Search;
use wcf\system\dashboard\DashboardHandler;
use wcf\system\database\PostgreSQLDatabase;
use wcf\system\exception\IllegalLinkException;
use wcf\system\request\LinkHandler;
use wcf\system\user\collapsible\content\UserCollapsibleContentHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Shows members page.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	page
 * @category	Community Framework
 */
class MembersListPage extends SortablePage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.user.members';
	
	/**
	 * available letters
	 * @var	string
	 */
	public static $availableLetters = '#ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('user.profile.canViewMembersList');
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededModules
	 */
	public $neededModules = array('MODULE_MEMBERS_LIST');
	
	/**
	 * @see	\wcf\page\AbstractPage::$enableTracking
	 */
	public $enableTracking = true;
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$itemsPerPage
	 */
	public $itemsPerPage = MEMBERS_LIST_USERS_PER_PAGE;
	
	/**
	 * @see	\wcf\page\SortablePage::$defaultSortField
	 */
	public $defaultSortField = MEMBERS_LIST_DEFAULT_SORT_FIELD;
	
	/**
	 * @see	\wcf\page\SortablePage::$defaultSortOrder
	 */
	public $defaultSortOrder = MEMBERS_LIST_DEFAULT_SORT_ORDER;
	
	/**
	 * @see	\wcf\page\SortablePage::$validSortFields
	 */
	public $validSortFields = array('username', 'registrationDate', 'activityPoints', 'likesReceived', 'lastActivityTime');
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$objectListClassName
	 */
	public $objectListClassName = 'wcf\data\user\UserProfileList';
	
	/**
	 * letter
	 * @var	string
	 */
	public $letter = '';
	
	/**
	 * id of a user search
	 * @var	integer
	 */
	public $searchID = 0;
	
	/**
	 * user search
	 * @var	\wcf\data\search\Search
	 */
	public $search = null;
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// letter
		if (isset($_REQUEST['letter']) && mb_strlen($_REQUEST['letter']) == 1 && mb_strpos(self::$availableLetters, $_REQUEST['letter']) !== false) {
			$this->letter = $_REQUEST['letter'];
		}
		
		if (!empty($_REQUEST['id'])) {
			$this->searchID = intval($_REQUEST['id']);
			$this->search = new Search($this->searchID);
			if (!$this->search->searchID || $this->search->userID != WCF::getUser()->userID || $this->search->searchType != 'users') {
				throw new IllegalLinkException();
			}
		}
		
		if (!empty($_POST)) {
			$parameters = http_build_query($_POST, '', '&');
			HeaderUtil::redirect(LinkHandler::getInstance()->getLink('MembersList', array(), $parameters));
			exit;
		}
	}
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::initObjectList()
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		if ($this->search !== null) {
			$searchData = unserialize($this->search->searchData);
			$this->objectList->getConditionBuilder()->add("user_table.userID IN (?)", array($searchData['matches']));
			unset($searchData);
		}
		
		if (!empty($this->letter)) {
			if ($this->letter == '#') {
				// PostgreSQL
				if (WCF::getDB() instanceof PostgreSQLDatabase) {
					$this->objectList->getConditionBuilder()->add("SUBSTRING(username FROM 1 for 1) IN ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9')");
				}
				else {
					// MySQL
					$this->objectList->getConditionBuilder()->add("SUBSTRING(username,1,1) IN ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9')");
				}
			}
			else {
				$this->objectList->getConditionBuilder()->add("username LIKE ?", array($this->letter.'%'));
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
			'letters' => str_split(self::$availableLetters),
			'letter' => $this->letter,
			'searchID' => $this->searchID,
			'sidebarCollapsed' => UserCollapsibleContentHandler::getInstance()->isCollapsed('com.woltlab.wcf.collapsibleSidebar', 'com.woltlab.wcf.user.MembersListPage'),
			'sidebarName' => 'com.woltlab.wcf.user.MembersListPage',
			'allowSpidersToIndexThisPage' => true
		));
		
		if (count($this->objectList) === 0) {
			@header('HTTP/1.0 404 Not Found');
		}
	}
}
