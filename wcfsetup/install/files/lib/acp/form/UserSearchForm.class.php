<?php
namespace wcf\acp\form;
use wcf\data\search\SearchEditor;
use wcf\form\AbstractForm;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\event\EventHandler;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Shows the user search form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class UserSearchForm extends UserOptionListForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.user.search';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.user.canSearchUser');
	
	/**
	 * username
	 * @var	string
	 */
	public $username = '';
	
	/**
	 * email address
	 * @var	string
	 */
	public $email = '';
	
	/**
	 * user id
	 * @var	integer
	 */
	public $userID = null;
	
	/**
	 * group ids
	 * @var	array<integer>
	 */
	public $groupIDs = array();
	
	/**
	 * true to invert the given group ids
	 * @var	boolean
	 */
	public $invertGroupIDs = 0;
	
	/**
	 * language ids
	 * @var	array<integer>
	 */
	public $languageIDs = array();
	
	/**
	 * registration start date
	 * @var	string
	 */
	public $registrationDateStart = '';
	
	/**
	 * registration start date
	 * @var	string
	 */
	public $registrationDateEnd = '';
	
	/**
	 * banned state
	 * @var	boolean
	 */
	public $banned = 0;
	
	/**
	 * not banned state
	 * @var	boolean
	 */
	public $notBanned = 0;
	
	/**
	 * last activity start time
	 * @var	string
	 */
	public $lastActivityTimeStart = '';
	
	/**
	 * last activity end time
	 * @var	string
	 */
	public $lastActivityTimeEnd = '';
	
	/**
	 * enabled state
	 * @var	boolean
	 */
	public $enabled = 0;
	
	/**
	 * disabled state
	 * @var	boolean
	 */
	public $disabled = 0;
	
	/**
	 * matches
	 * @var	array<integer>
	 */
	public $matches = array();
	
	/**
	 * condtion builder object
	 * @var	\wcf\system\database\condition\PreparedStatementConditionBuilder
	 */
	public $conditions = null;
	
	/**
	 * search id
	 * @var	integer
	 */
	public $searchID = 0;
	
	/**
	 * sort field
	 * @var	string
	 */
	public $sortField = 'username';
	
	/**
	 * sort order
	 * @var	string
	 */
	public $sortOrder = 'ASC';
	
	/**
	 * results per page
	 * @var	integer
	 */
	public $itemsPerPage = 50;
	
	/**
	 * shown columns
	 * @var	array<string>
	 */
	public $columns = array('registrationDate', 'lastActivityTime');
	
	/**
	 * number of results
	 * @var	integer
	 */
	public $maxResults = 0;
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// search user from passed groupID by group-view
		if (isset($_GET['groupID'])) {
			$this->groupIDs[] = intval($_GET['groupID']);
			
			// disable check for security token for GET requests
			$_POST['t'] = WCF::getSession()->getSecurityToken();
			
			// do search
			try {
				$this->validate();
				$this->save();
			}
			catch (UserInputException $e) {
				$this->errorField = $e->getField();
				$this->errorType = $e->getType();
			}
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['username'])) $this->username = StringUtil::trim($_POST['username']);
		if (isset($_POST['email'])) $this->email = StringUtil::trim($_POST['email']);
		if (!empty($_POST['userID'])) $this->userID = intval($_POST['userID']);
		if (isset($_POST['groupIDs']) && is_array($_POST['groupIDs'])) $this->groupIDs = ArrayUtil::toIntegerArray($_POST['groupIDs']);
		if (isset($_POST['languageIDs']) && is_array($_POST['languageIDs'])) $this->languageIDs = ArrayUtil::toIntegerArray($_POST['languageIDs']);
		if (isset($_POST['invertGroupIDs'])) $this->invertGroupIDs = intval($_POST['invertGroupIDs']);
		if (isset($_POST['registrationDateStart'])) $this->registrationDateStart = $_POST['registrationDateStart'];
		if (isset($_POST['registrationDateEnd'])) $this->registrationDateEnd = $_POST['registrationDateEnd'];
		if (isset($_POST['banned'])) $this->banned = intval($_POST['banned']);
		if (isset($_POST['notBanned'])) $this->notBanned = intval($_POST['notBanned']);
		if (isset($_POST['lastActivityTimeStart'])) $this->lastActivityTimeStart = $_POST['lastActivityTimeStart'];
		if (isset($_POST['lastActivityTimeEnd'])) $this->lastActivityTimeEnd = $_POST['lastActivityTimeEnd'];
		if (isset($_POST['enabled'])) $this->enabled = intval($_POST['enabled']);
		if (isset($_POST['disabled'])) $this->disabled = intval($_POST['disabled']);
		
		if (isset($_POST['itemsPerPage'])) $this->itemsPerPage = intval($_POST['itemsPerPage']);
		if (isset($_POST['sortField'])) $this->sortField = $_POST['sortField'];
		if (isset($_POST['sortOrder'])) $this->sortOrder = $_POST['sortOrder'];
		if (isset($_POST['columns']) && is_array($_POST['columns'])) $this->columns = $_POST['columns'];
	}
	
	/**
	 * @see	\wcf\acp\form\AbstractOptionListForm::initOptionHandler()
	 */
	protected function initOptionHandler() {
		$this->optionHandler->enableSearchMode();
		$this->optionHandler->init();
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		// add email column for authorized users
		if (WCF::getSession()->getPermission('admin.user.canEditMailAddress')) {
			array_unshift($this->columns, 'email');
		}
		
		$this->readOptionTree();
	}
	
	/**
	 * Reads option tree on page init.
	 */
	protected function readOptionTree() {
		$this->optionTree = $this->optionHandler->getOptionTree();
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'username' => $this->username,
			'email' => $this->email,
			'userID' => $this->userID,
			'groupIDs' => $this->groupIDs,
			'languageIDs' => $this->languageIDs,
			'optionTree' => $this->optionTree,
			'availableGroups' => $this->getAvailableGroups(),
			'availableLanguages' => LanguageFactory::getInstance()->getLanguages(),
			'invertGroupIDs' => $this->invertGroupIDs,
			'registrationDateStart' => $this->registrationDateStart,
			'registrationDateEnd' => $this->registrationDateEnd,
			'banned' => $this->banned,
			'notBanned' => $this->notBanned,
			'sortField' => $this->sortField,
			'sortOrder' => $this->sortOrder,
			'itemsPerPage' => $this->itemsPerPage,
			'columns' => $this->columns,
			'lastActivityTimeStart' => $this->lastActivityTimeStart,
			'lastActivityTimeEnd' => $this->lastActivityTimeEnd,
			'enabled' => $this->enabled,
			'disabled' => $this->disabled,
			'columnOptions' => $this->optionHandler->getCategoryOptions('profile')
		));
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// store search result in database
		$data = serialize(array(
			'matches' => $this->matches,
			'itemsPerPage' => $this->itemsPerPage,
			'columns' => $this->columns
		));
		
		$search = SearchEditor::create(array(
			'userID' => WCF::getUser()->userID,
			'searchData' => $data,
			'searchTime' => TIME_NOW,
			'searchType' => 'users'
		));
		
		// get new search id
		$this->searchID = $search->searchID;
		$this->saved();
		
		// forward to result page
		$url = LinkHandler::getInstance()->getLink('UserList', array('id' => $this->searchID), 'sortField='.rawurlencode($this->sortField).'&sortOrder='.rawurlencode($this->sortOrder));
		HeaderUtil::redirect($url);
		exit;
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		AbstractForm::validate();
		
		// do search
		$this->search();
		
		if (empty($this->matches)) {
			throw new UserInputException('search', 'noMatches');
		}
	}
	
	/**
	 * Search for users which fit to the search values.
	 */
	protected function search() {
		$this->matches = array();
		$sql = "SELECT		user_table.userID
			FROM		wcf".WCF_N."_user user_table
			LEFT JOIN	wcf".WCF_N."_user_option_value option_value
			ON		(option_value.userID = user_table.userID)";
		
		// build search condition
		$this->conditions = new PreparedStatementConditionBuilder();
		
		// static fields
		$this->buildStaticConditions();
		
		// dynamic fields
		$this->buildDynamicConditions();
		
		// call buildConditions event
		EventHandler::getInstance()->fireAction($this, 'buildConditions');
		
		// do search
		$statement = WCF::getDB()->prepareStatement($sql.$this->conditions, $this->maxResults);
		$statement->execute($this->conditions->getParameters());
		while ($row = $statement->fetchArray()) {
			$this->matches[] = $row['userID'];
		}
	}
	
	/**
	 * Builds the static conditions.
	 */
	protected function buildStaticConditions() {
		if (!empty($this->username)) {
			$this->conditions->add("user_table.username LIKE ?", array('%'.addcslashes($this->username, '_%').'%'));
		}
		if (!empty($this->userID)) {
			$this->conditions->add("user_table.userID = ?", array($this->userID));
		}
		if (!empty($this->email)) {
			$this->conditions->add("user_table.email LIKE ?", array('%'.addcslashes($this->email, '_%').'%'));
		}
		if (!empty($this->groupIDs)) {
			$this->conditions->add("user_table.userID ".($this->invertGroupIDs == 1 ? 'NOT ' : '')."IN (SELECT userID FROM wcf".WCF_N."_user_to_group WHERE groupID IN (?))", array($this->groupIDs));
		}
		if (!empty($this->languageIDs)) {
			$this->conditions->add("user_table.languageID IN (?)", array($this->languageIDs));
		}
		
		// registration date
		if ($startDate = @strtotime($this->registrationDateStart)) {
			$this->conditions->add('user_table.registrationDate >= ?', array($startDate));
		}
		if ($endDate = @strtotime($this->registrationDateEnd)) {
			$this->conditions->add('user_table.registrationDate <= ?', array($endDate));
		}
		
		if ($this->banned) {
			$this->conditions->add('user_table.banned = ?', array(1));
		}
		if ($this->notBanned) {
			$this->conditions->add('user_table.banned = ?', array(0));
		}
		
		// last activity time
		if ($startDate = @strtotime($this->lastActivityTimeStart)) {
			$this->conditions->add('user_table.lastActivityTime >= ?', array($startDate));
		}
		if ($endDate = @strtotime($this->lastActivityTimeEnd)) {
			$this->conditions->add('user_table.lastActivityTime <= ?', array($endDate));
		}
		
		if ($this->enabled) {
			$this->conditions->add('user_table.activationCode = ?', array(0));
		}
		if ($this->disabled) {
			$this->conditions->add('user_table.activationCode <> ?', array(0));
		}
	}
	
	/**
	 * Builds the dynamic conditions.
	 */
	protected function buildDynamicConditions() {
		foreach ($this->optionHandler->getCategoryOptions('profile') as $option) {
			$option = $option['object'];
			
			$value = isset($this->optionHandler->optionValues[$option->optionName]) ? $this->optionHandler->optionValues[$option->optionName] : null;
			$this->optionHandler->getTypeObject($option->optionType)->getCondition($this->conditions, $option, $value);
		}
	}
}
