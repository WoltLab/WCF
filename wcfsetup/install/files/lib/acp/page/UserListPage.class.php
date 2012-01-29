<?php
namespace wcf\acp\page;
use wcf\system\menu\acp\ACPMenu;
use wcf\data\user\User;
use wcf\data\user\group\UserGroup;
use wcf\data\user\option\ViewableUserOption;
use wcf\page\SortablePage;
use wcf\system\cache\CacheHandler;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\event\EventHandler;
use wcf\system\exception\SystemException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\DateUtil;
use wcf\util\StringUtil;
use wcf\system\exception\IllegalLinkException;

/**
 * Shows the result of a user search.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class UserListPage extends SortablePage {
	/**
	 * @see wcf\page\AbstractPage::$templateName
	 */
	public $templateName = 'userList';
	
	/**
	 * @see wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.user.canSearchUser');
	
	/**
	 * @see wcf\page\MultipleLinkPage::$itemsPerPage
	 */
	public $itemsPerPage = 50;
	
	/**
	 * @see wcf\page\SortablePage::$defaultSortField
	 */
	public $defaultSortField = 'username';
	
	/**
	 * @see wcf\page\SortablePage::$validSortFields
	 */
	public $validSortFields = array('email', 'userID', 'registrationDate', 'username');
	
	/**
	 * id of a user search
	 * @var	integer
	 */
	public $searchID = 0;
	
	// data
	public $userIDs = array();
	public $markedUsers = array();
	public $users = array();
	public $url = '';
	public $columns = array('email', 'registrationDate');
	public $options = array();
	public $columnValues = array();
	public $columnHeads = array();
	public $sqlConditions = '';
	
	protected $optionHandler = null;
	
	/**
	 * @see wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->conditions = new PreparedStatementConditionBuilder();
		
		if (!empty($_REQUEST['id'])) {
			$this->searchID = intval($_REQUEST['id']);
			if ($this->searchID) $this->readSearchResult();
			if (!count($this->userIDs)) {
				throw new IllegalLinkException();
			}
			$this->conditions->add("user_table.userID IN (?)", array($this->userIDs));
		}
		
		// get user options
		$this->readUserOptions();
	}
	
	/**
	 * @see wcf\page\SortablePage::validateSortField()
	 */
	public function validateSortField() {
		// add options to valid sort fields
		$this->validSortFields = array_merge($this->validSortFields, array_keys($this->options));
		
		parent::validateSortField();
	}
	
	/**
	 * @see wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		// get marked users
		$this->markedUsers = WCF::getSession()->getVar('markedUsers');
		if ($this->markedUsers == null || !is_array($this->markedUsers)) $this->markedUsers = array();
		
		// get columns heads
		$this->readColumnsHeads();
		
		// get users
		$this->readUsers();
		
		// build page url
		$this->url = LinkHandler::getInstance()->getLink('UserList', array(), 'searchID='.$this->searchID.'&action='.rawurlencode($this->action).'&pageNo='.$this->pageNo.'&sortField='.$this->sortField.'&sortOrder='.$this->sortOrder);
	}
	
	/**
	 * @see wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'users' => $this->users,
			'searchID' => $this->searchID,
			'hasMarkedItems' => ClipboardHandler::getInstance()->hasMarkedItems(),
			'url' => $this->url,
			'columnHeads' => $this->columnHeads,
			'columnValues' => $this->columnValues
		));
	}
	
	/**
	 * @see wcf\page\IPage::show()
	 */
	public function show() {
		// set active menu item
		ACPMenu::getInstance()->setActiveMenuItem('wcf.acp.menu.link.user.'.($this->searchID ? 'search' : 'list'));
		
		parent::show();
	}
	
	/**
	 * @see wcf\page\MultipleLinkPage::countItems()
	 */
	public function countItems() {
		// call countItems event
		EventHandler::getInstance()->fireAction($this, 'countItems');

		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_user user_table
			".$this->conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($this->conditions->getParameters());
		$row = $statement->fetchArray();
		return $row['count'];
	}
	
	/**
	 * Gets the list of results.
	 */
	protected function readUsers() {
		// get user ids
		$userIDs = array();
		$sql = "SELECT		user_table.userID
			FROM		wcf".WCF_N."_user user_table
			".(isset($this->options[$this->sortField]) ? "LEFT JOIN wcf".WCF_N."_user_option_value user_option_value ON (user_option_value.userID = user_table.userID)" : '')."
			".$this->conditions."
			ORDER BY	".(($this->sortField != 'email' && isset($this->options[$this->sortField])) ? 'user_option_value.userOption'.$this->options[$this->sortField]['optionID'] : $this->sortField)." ".$this->sortOrder;
		$statement = WCF::getDB()->prepareStatement($sql, $this->itemsPerPage, ($this->pageNo - 1) * $this->itemsPerPage);
		$statement->execute($this->conditions->getParameters());
		while ($row = $statement->fetchArray()) {
			$userIDs[] = $row['userID'];
		}
		
		// get user data
		if (count($userIDs)) {
			$userToGroups = array();
			
			// get group ids
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("user_table.userID IN (?)", array($userIDs));
			
			$sql = "SELECT	userID, groupID
				FROM	wcf".WCF_N."_user_to_group user_table
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			while ($row = $statement->fetchArray()) {
				$userToGroups[$row['userID']][] = $row['groupID'];
			}
			
			$sql = "SELECT		option_value.*, user_table.*
				FROM		wcf".WCF_N."_user user_table
				LEFT JOIN	wcf".WCF_N."_user_option_value option_value
				ON		(option_value.userID = user_table.userID)
				".$conditions."
				ORDER BY	".(($this->sortField != 'email' && isset($this->options[$this->sortField])) ? 'option_value.userOption'.$this->options[$this->sortField]['optionID'] : 'user_table.'.$this->sortField)." ".$this->sortOrder;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			while ($row = $statement->fetchArray()) {
				$row['groupIDs'] = implode(',', $userToGroups[$row['userID']]);
				$accessible = UserGroup::isAccessibleGroup($userToGroups[$row['userID']]);
				$row['accessible'] = $accessible;
				$row['deletable'] = ($accessible && WCF::getSession()->getPermission('admin.user.canDeleteUser') && $row['userID'] != WCF::getUser()->userID) ? 1 : 0;
				$row['editable'] = ($accessible && WCF::getSession()->getPermission('admin.user.canEditUser')) ? 1 : 0;
				$row['isMarked'] = intval(in_array($row['userID'], $this->markedUsers));
				
				$this->users[] = new User(null, $row);
			}
			
			// get special columns
			foreach ($this->users as $key => $user) {
				foreach ($this->columns as $column) {
					switch ($column) {
						case 'email':
							$this->columnValues[$user->userID][$column] = '<a href="mailto:'.StringUtil::encodeHTML($user->email).'">'.StringUtil::encodeHTML($user->email).'</a>';
						break;
						
						case 'registrationDate':
							$this->columnValues[$user->userID][$column] = DateUtil::format(DateUtil::getDateTimeByTimestamp($user->{$column}), DateUtil::DATE_FORMAT);
						break;
						
						default:
							if (isset($this->options[$column])) {
								if ($this->options[$column]->outputClass) {
									$this->options[$column]->setOptionValue($user);
									$outputObj = $this->options[$column]->getOutputObject();
									$this->columnValues[$user->userID][$column] = $outputObj->getOutput($user, $this->options[$column]->getDecoratedObject(), $user->{$column});
								}
								else {
									$this->columnValues[$user->userID][$column] = StringUtil::encodeHTML($user->{$column});
								}
							}
						break;
					}
				}
			}
		}
	}
	
	/**
	 * Gets the result of the search with the given search id.
	 */
	protected function readSearchResult() {
		// get user search from database
		$sql = "SELECT	searchData
			FROM	wcf".WCF_N."_search
			WHERE	searchID = ?
				AND userID = ?
				AND searchType = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$this->searchID,
			WCF::getUser()->userID,
			'users'
		));
		$search = $statement->fetchArray();
		if (!isset($search['searchData'])) {
			throw new IllegalLinkException();
		}
		
		$data = unserialize($search['searchData']);
		$this->userIDs = $data['matches'];
		$this->itemsPerPage = $data['itemsPerPage'];
		$this->columns = $data['columns'];
		unset($data);
	}
	
	/**
	 * Gets the user options from cache.
	 */
	protected function readUserOptions() {
		$cacheName = 'user-option-'.PACKAGE_ID;
		CacheHandler::getInstance()->addResource(
			$cacheName,
			WCF_DIR.'cache/cache.'.$cacheName.'.php',
			'wcf\system\cache\builder\OptionCacheBuilder'
		);
		$this->options = CacheHandler::getInstance()->get($cacheName, 'options');
		
		foreach ($this->options as &$option) {
			$option = new ViewableUserOption($option);
		}
		unset($option);
	}
	
	/**
	 * Reads the column heads.
	 */
	protected function readColumnsHeads() {
		foreach ($this->columns as $column) {
			if (isset($this->options[$column])) {
				$this->columnHeads[$column] = 'wcf.user.option.'.$column;
			}
			else {
				$this->columnHeads[$column] = 'wcf.user.'.$column;
			}
		}
	}
	
	/**
	 * @see	wcf\page\MultipleLinkPage::initObjectList()
	 */		
	protected function initObjectList() { }
	
	/**
	 * @see	wcf\page\MultipleLinkPage::readObjects()
	 */	
	protected function readObjects() { }
}
