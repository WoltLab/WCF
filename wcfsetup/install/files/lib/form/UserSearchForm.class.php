<?php
namespace wcf\form;
use wcf\acp\form\UserOptionListForm;
use wcf\data\search\SearchEditor;
use wcf\system\breadcrumb\Breadcrumb;
use wcf\system\dashboard\DashboardHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\request\LinkHandler;
use wcf\system\user\collapsible\content\UserCollapsibleContentHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Shows the user search form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	form
 * @category	Community Framework
 */
class UserSearchForm extends UserOptionListForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.user.search';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededModules
	 */
	public $neededModules = array('MODULE_MEMBERS_LIST');
	
	/**
	 * username
	 * @var	string
	 */
	public $username = '';
	
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
	 * number of results
	 * @var	integer
	 */
	public $maxResults = 1000;
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['username'])) $this->username = StringUtil::trim($_POST['username']);
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
		
		$this->readOptionTree();
		
		// add breadcrumbs
		WCF::getBreadcrumbs()->add(new Breadcrumb(WCF::getLanguage()->get('wcf.user.members'), LinkHandler::getInstance()->getLink('MembersList')));
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
		
		DashboardHandler::getInstance()->loadBoxes('com.woltlab.wcf.user.MembersListPage', $this);
		
		WCF::getTPL()->assign(array(
			'username' => $this->username,
			'optionTree' => $this->optionTree,
			'sidebarCollapsed' => UserCollapsibleContentHandler::getInstance()->isCollapsed('com.woltlab.wcf.collapsibleSidebar', 'com.woltlab.wcf.user.MembersListPage'),
			'sidebarName' => 'com.woltlab.wcf.user.MembersListPage'
		));
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// store search result in database
		$search = SearchEditor::create(array(
			'userID' => WCF::getUser()->userID ?: null,
			'searchData' => serialize(array('matches' => $this->matches)),
			'searchTime' => TIME_NOW,
			'searchType' => 'users'
		));
		
		// get new search id
		$this->searchID = $search->searchID;
		$this->saved();
		
		// forward to result page
		$url = LinkHandler::getInstance()->getLink('MembersList', array('id' => $this->searchID));
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
		
		// if no conditions exists, no need to send query
		if (!count($this->conditions->getParameters())) {
			return;
		}
		
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
