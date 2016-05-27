<?php
namespace wcf\form;
use wcf\acp\form\UserOptionListForm;
use wcf\data\search\SearchEditor;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\page\PageLocationManager;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Shows the user search form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	form
 * @category	Community Framework
 */
class UserSearchForm extends UserOptionListForm {
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_MEMBERS_LIST'];
	
	/**
	 * username
	 * @var	string
	 */
	public $username = '';
	
	/**
	 * matches
	 * @var	integer[]
	 */
	public $matches = [];
	
	/**
	 * condition builder object
	 * @var	PreparedStatementConditionBuilder
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
	 * option tree
	 * @var	array
	 */
	public $optionTree = [];
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['username'])) $this->username = StringUtil::trim($_POST['username']);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initOptionHandler() {
		$this->optionHandler->enableSearchMode();
		$this->optionHandler->init();
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		$this->readOptionTree();
		
		// add breadcrumbs
		if (MODULE_MEMBERS_LIST) PageLocationManager::getInstance()->addParentLocation('com.woltlab.wcf.MembersList');
	}
	
	/**
	 * Reads option tree on page init.
	 */
	protected function readOptionTree() {
		$this->optionTree = $this->optionHandler->getOptionTree();
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'username' => $this->username,
			'optionTree' => $this->optionTree
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		// store search result in database
		$search = SearchEditor::create([
			'userID' => WCF::getUser()->userID ?: null,
			'searchData' => serialize(['matches' => $this->matches]),
			'searchTime' => TIME_NOW,
			'searchType' => 'users'
		]);
		
		// get new search id
		$this->searchID = $search->searchID;
		$this->saved();
		
		// forward to result page
		$url = LinkHandler::getInstance()->getLink('MembersList', ['id' => $this->searchID]);
		HeaderUtil::redirect($url);
		exit;
	}
	
	/**
	 * @inheritDoc
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
		$this->matches = [];
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
		if (!$this->conditions->__toString()) {
			return;
		}
		
		// do search
		$statement = WCF::getDB()->prepareStatement($sql.$this->conditions, $this->maxResults);
		$statement->execute($this->conditions->getParameters());
		$this->matches = $statement->fetchAll(\PDO::FETCH_COLUMN);
	}
	
	/**
	 * Builds the static conditions.
	 */
	protected function buildStaticConditions() {
		if (!empty($this->username)) {
			$this->conditions->add("user_table.username LIKE ?", ['%'.addcslashes($this->username, '_%').'%']);
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
