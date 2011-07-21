<?php
namespace wcf\acp\form;
use wcf\system\menu\acp\ACPMenu;
use wcf\data\option\Option;
use wcf\data\search\SearchEditor;
use wcf\form\AbstractForm;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\event\EventHandler;
use wcf\system\exception\UserInputException;
use wcf\system\wcf;
use wcf\util\ArrayUtil;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Shows the user search form.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class UserSearchForm extends UserOptionListForm {
	/**
	 * @see wcf\page\AbstractPage::$templateName
	 */
	public $templateName = 'userSearch';
	
	/**
	 * active menu item name
	 * @var string
	 */
	public $menuItemName = 'wcf.acp.menu.link.user.search';
	
	/**
	 * @see wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.user.canSearchUser');
	
	/**
	 * username 
	 * @var string
	 */
	public $username = '';
	
	/**
	 * email address
	 * @var string
	 */
	public $email = '';
	
	/**
	 * user id
	 * @var integer
	 */
	public $userID = 0;

	/**
	 * group ids
	 * @var array<integer>
	 */
	public $groupIDs = array();
	
	/**
	 * true to invert the given group ids
	 * @var boolean
	 */
	public $invertGroupIDs = 0;
	
	/**
	 * language ids
	 * @var array<integer>
	 */
	public $languageIDs = array();
	
	/**
	 * matches
	 * @var array<integer>
	 */
	public $matches = array();
	
	/**
	 * condtion builder object
	 * @var wcf\system\database\condition\PreparedStatementConditionBuilder
	 */
	public $conditions = null;
	
	/**
	 * search id
	 * @var integer
	 */
	public $searchID = 0;
	
	/**
	 * sort field
	 * @var string
	 */
	public $sortField = 'username';
	
	/**
	 * sort order
	 * @var string
	 */
	public $sortOrder = 'ASC';
	
	/**
	 * results per page
	 * @var integer
	 */
	public $itemsPerPage = 50;
	
	/**
	 * shown columns
	 * @var array<string>
	 */
	public $columns = array('email', 'registrationDate');
	
	/**
	 * number of results
	 * @var integer
	 */
	public $maxResults = 0;
	
	/**
	 * @see wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['username'])) $this->username = StringUtil::trim($_POST['username']);
		if (isset($_POST['email'])) $this->email = StringUtil::trim($_POST['email']);
		if (isset($_POST['userID'])) $this->userID = intval($_POST['userID']);
		if (isset($_POST['groupIDs']) && is_array($_POST['groupIDs'])) $this->groupIDs = ArrayUtil::toIntegerArray($_POST['groupIDs']);
		if (isset($_POST['languageIDs']) && is_array($_POST['languageIDs'])) $this->languageIDs = ArrayUtil::toIntegerArray($_POST['languageIDs']);
		if (isset($_POST['invertGroupIDs'])) $this->invertGroupIDs = intval($_POST['invertGroupIDs']);
		
		if (isset($_POST['itemsPerPage'])) $this->itemsPerPage = intval($_POST['itemsPerPage']);
		if (isset($_POST['sortField'])) $this->sortField = $_POST['sortField'];
		if (isset($_POST['sortOrder'])) $this->sortOrder = $_POST['sortOrder'];
		if (isset($_POST['columns']) && is_array($_POST['columns'])) $this->columns = $_POST['columns'];
	}
	
	/**
	 * @see wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->optionTree = $this->getCategoryOptions('profile');
	}
	
	/**
	 * @see wcf\page\IPage::assignVariables()
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
			'availableLanguages' => $this->getAvailablelanguages(),
			'invertGroupIDs' => $this->invertGroupIDs,
			'sortField' => $this->sortField,
			'sortOrder' => $this->sortOrder,
			'itemsPerPage' => $this->itemsPerPage,
			'columns' => $this->columns
		));
	}
	
	/**
	 * @see wcf\form\IForm::show()
	 */
	public function show() {
		// set active menu item
		ACPMenu::getInstance()->setActiveMenuItem($this->menuItemName);
		
		// get user options and categories from cache
		$this->readCache();
		
		// show form
		parent::show();
	}
	
	/**
	 * @see wcf\form\IForm::save()
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
		HeaderUtil::redirect('index.php?page=UserList&searchID='.$this->searchID.'&sortField='.rawurlencode($this->sortField).'&sortOrder='.rawurlencode($this->sortOrder).''.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
	
	/**
	 * @see wcf\form\IForm::validate()
	 */
	public function validate() {
		AbstractForm::validate();
		
		// do search
		$this->search();
		
		if (count($this->matches) == 0) {
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
			$this->conditions->add("user_table.userID LIKE ?", array('%'.$this->userID.'%'));
		}
		if (!empty($this->email)) {
			$this->conditions->add("user_table.email LIKE ?", array('%'.addcslashes($this->email, '_%').'%'));
		}
		if (count($this->groupIDs)) {
			$this->conditions->add("user_table.userID ".($this->invertGroupIDs == 1 ? 'NOT ' : '')."IN (SELECT userID FROM wcf".WCF_N."_user_to_group WHERE groupID IN (?))", array($this->groupIDs));
		}
		if (count($this->languageIDs)) {
			$this->conditions->add("user_table.languageID IN (?)", array($this->languageIDs));
		}
	}
	
	/**
	 * Builds the dynamic conditions.
	 */
	protected function buildDynamicConditions() {
		foreach ($this->options as $option) {
			$value = isset($this->values[$option->optionName]) ? $this->values[$option->optionName] : null;
			$condition = $this->getTypeObject($option->optionType)->getCondition($option, $value);
			if ($condition !== false) $this->conditions->add($condition);
		}
	}
	
	/**
	 * @see wcf\system\option\SearchableOptionType::getSearchFormElement()
	 */
	protected function getFormElement($type, Option $option) {
		return $this->getTypeObject($type)->getSearchFormElement($option);
	}
	
	/**
	 * @see wcf\acp\form\DynamicOptionListForm::checkOption()
	 */
	protected static function checkOption(Option $option) {
		return ($option->searchable == 1 && !$option->disabled && ($option->visible == 3 || $option->visible < 2));
	}
}
