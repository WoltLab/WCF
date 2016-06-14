<?php
namespace wcf\acp\form;
use wcf\data\condition\Condition;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\search\SearchEditor;
use wcf\data\user\group\UserGroup;
use wcf\data\user\UserList;
use wcf\form\AbstractForm;
use wcf\system\condition\IUserCondition;
use wcf\system\condition\UserGroupCondition;
use wcf\system\event\EventHandler;
use wcf\system\exception\UserInputException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Shows the user search form.
 * 
 * @author	Matthias Schmidt, Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class UserSearchForm extends UserOptionListForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.user.search';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.user.canSearchUser'];
	
	/**
	 * list of grouped user group assignment condition object types
	 * @var	ObjectType[][]
	 */
	public $conditions = [];
	
	/**
	 * list with searched users
	 * @var	\wcf\data\user\UserList
	 */
	public $userList = null;
	
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
	 * @var	string[]
	 */
	public $columns = ['registrationDate', 'lastActivityTime'];
	
	/**
	 * number of results
	 * @var	integer
	 */
	public $maxResults = 2000;
	
	/**
	 * id of the group the users have to belong to
	 * is used on the user group list to show all users of a user group
	 * @var	integer
	 */
	public $groupID = 0;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		// search user from passed groupID by group-view
		if (isset($_GET['groupID'])) {
			$this->groupID = intval($_GET['groupID']);
			
			// disable check for security token for GET requests
			$_POST['t'] = WCF::getSession()->getSecurityToken();
			
			// do search
			try {
				$this->readData();
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
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		foreach ($this->conditions as $conditions) {
			foreach ($conditions as $condition) {
				$condition->getProcessor()->readFormParameters();
			}
		}
		
		if (isset($_POST['itemsPerPage'])) $this->itemsPerPage = intval($_POST['itemsPerPage']);
		if (isset($_POST['sortField'])) $this->sortField = $_POST['sortField'];
		if (isset($_POST['sortOrder'])) $this->sortOrder = $_POST['sortOrder'];
		if (isset($_POST['columns']) && is_array($_POST['columns'])) $this->columns = $_POST['columns'];
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		$objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.condition.userSearch');
		foreach ($objectTypes as $objectType) {
			if (!$objectType->conditiongroup) continue;
			
			if (!isset($this->conditions[$objectType->conditiongroup])) {
				$this->conditions[$objectType->conditiongroup] = [];
			}
			
			$this->conditions[$objectType->conditiongroup][$objectType->objectTypeID] = $objectType;
		}
		
		parent::readData();
		
		// add email column for authorized users
		if (WCF::getSession()->getPermission('admin.user.canEditMailAddress')) {
			array_unshift($this->columns, 'email');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'groupedObjectTypes' => $this->conditions,
			'sortField' => $this->sortField,
			'sortOrder' => $this->sortOrder,
			'itemsPerPage' => $this->itemsPerPage,
			'columns' => $this->columns,
			'columnOptions' => $this->optionHandler->getCategoryOptions('profile')
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		// store search result in database
		$data = serialize([
			'matches' => $this->userList->getObjectIDs(),
			'itemsPerPage' => $this->itemsPerPage,
			'columns' => $this->columns
		]);
		
		$search = SearchEditor::create([
			'userID' => WCF::getUser()->userID,
			'searchData' => $data,
			'searchTime' => TIME_NOW,
			'searchType' => 'users'
		]);
		
		// get new search id
		$this->searchID = $search->searchID;
		$this->saved();
		
		// forward to result page
		HeaderUtil::redirect(LinkHandler::getInstance()->getLink('UserList', [
			'id' => $this->searchID
		], 'sortField='.rawurlencode($this->sortField).'&sortOrder='.rawurlencode($this->sortOrder)));
		exit;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		AbstractForm::validate();
		
		// remove email column for non-authorized users
		if (!WCF::getSession()->getPermission('admin.user.canEditMailAddress') && ($key = array_search('email', $this->columns)) !== false) {
			unset($this->columns[$key]);
		}
		
		foreach ($this->conditions as $conditions) {
			/** @var ObjectType $objectType */
			foreach ($conditions as $objectType) {
				/** @var IUserCondition $processor */
				$processor = $objectType->getProcessor();
				
				// manually inject user group data for listing of group members
				if ($this->groupID && $objectType->objectType == 'com.woltlab.wcf.userGroup') {
					/** @var UserGroupCondition $processor */
					
					$userGroups = UserGroup::getAccessibleGroups([], [UserGroup::EVERYONE, UserGroup::GUESTS]);
					
					uasort($userGroups, function(UserGroup $groupA, UserGroup $groupB) {
						return strcmp($groupA->getName(), $groupB->getName());
					});
					
					$processor->setUserGroups($userGroups);
					$processor->setData(new Condition(null, [
						'conditionData' => serialize(['groupIDs' => [$this->groupID]])
					]));
				}
				$processor->validate();
			}
		}
		
		$this->search();
		
		if (!count($this->userList->getObjectIDs())) {
			throw new UserInputException('search', 'noMatches');
		}
	}
	
	/**
	 * Search for users which fit to the search values.
	 */
	protected function search() {
		$this->userList = new UserList();
		
		EventHandler::getInstance()->fireAction($this, 'search');
		
		// read user ids
		foreach ($this->conditions as $groupedObjectTypes) {
			foreach ($groupedObjectTypes as $objectType) {
				$data = $objectType->getProcessor()->getData();
				if ($data !== null) {
					$objectType->getProcessor()->addObjectListCondition($this->userList, $data);
				}
			}
		}
		$this->userList->readObjectIDs();
	}
}
