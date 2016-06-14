<?php
namespace wcf\acp\form;
use wcf\data\user\group\UserGroupAction;
use wcf\data\user\group\UserGroupEditor;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\option\user\group\UserGroupOptionHandler;
use wcf\system\WCF;
use wcf\system\WCFACP;
use wcf\util\StringUtil;

/**
 * Shows the group add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class UserGroupAddForm extends AbstractOptionListForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.group.add';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.user.canAddGroup'];
	
	/**
	 * option tree
	 * @var	array
	 */
	public $optionTree = [];
	
	/**
	 * @inheritDoc
	 */
	public $optionHandlerClassName = UserGroupOptionHandler::class;
	
	/**
	 * @inheritDoc
	 */
	public $supportI18n = false;
	
	/**
	 * group name
	 * @var	string
	 */
	public $groupName = '';
	
	/**
	 * group description
	 * @var	string
	 */
	protected $groupDescription = '';
	
	/**
	 * list of values of group 'Anyone'
	 * @var	array
	 */
	public $defaultValues = [];
	
	/**
	 * group priority
	 * @var	integer
	 */
	protected $priority = 0;
	
	/**
	 * user online marking string
	 * @var	string
	 */
	protected $userOnlineMarking = '%s';
	
	/**
	 * shows the members of this group on the team page
	 * @var	integer
	 */
	protected $showOnTeamPage = 0;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		I18nHandler::getInstance()->register('groupName');
		I18nHandler::getInstance()->register('groupDescription');
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		I18nHandler::getInstance()->readValues();
		
		if (I18nHandler::getInstance()->isPlainValue('groupName')) $this->groupName = I18nHandler::getInstance()->getValue('groupName');
		if (I18nHandler::getInstance()->isPlainValue('groupDescription')) $this->groupDescription = I18nHandler::getInstance()->getValue('groupDescription');
		
		if (isset($_POST['priority'])) $this->priority = intval($_POST['priority']);
		if (isset($_POST['userOnlineMarking'])) $this->userOnlineMarking = StringUtil::trim($_POST['userOnlineMarking']);
		if (isset($_POST['showOnTeamPage'])) $this->showOnTeamPage = intval($_POST['showOnTeamPage']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		// validate dynamic options
		parent::validate();
		
		// validate group name
		try {
			if (!I18nHandler::getInstance()->validateValue('groupName')) {
				if (I18nHandler::getInstance()->isPlainValue('groupName')) {
					throw new UserInputException('groupName');
				}
				else {
					throw new UserInputException('groupName', 'multilingual');
				}
			}
			if (mb_strpos($this->userOnlineMarking, '%s') === false) {
				throw new UserInputException('userOnlineMarking', 'notValid');
			}
		}
		catch (UserInputException $e) {
			$this->errorType[$e->getField()] = $e->getType();
		}
		
		if (!empty($this->errorType)) {
			throw new UserInputException('groupName', $this->errorType);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		$optionValues = $this->optionHandler->save();
		
		$data = [
			'data' => array_merge($this->additionalFields, [
				'groupName' => $this->groupName,
				'groupDescription' => $this->groupDescription,
				'priority' => $this->priority,
				'userOnlineMarking' => $this->userOnlineMarking,
				'showOnTeamPage' => $this->showOnTeamPage
			]),
			'options' => $optionValues
		];
		$this->objectAction = new UserGroupAction([], 'create', $data);
		$this->objectAction->executeAction();
		$returnValues = $this->objectAction->getReturnValues();
		$groupID = $returnValues['returnValues']->groupID;
		
		if (!I18nHandler::getInstance()->isPlainValue('groupName')) {
			I18nHandler::getInstance()->save('groupName', 'wcf.acp.group.group'.$groupID, 'wcf.acp.group', 1);
			
			// update group name
			$groupEditor = new UserGroupEditor($returnValues['returnValues']);
			$groupEditor->update([
				'groupName' => 'wcf.acp.group.group'.$groupID
			]);
		}
		if (!I18nHandler::getInstance()->isPlainValue('groupDescription')) {
			I18nHandler::getInstance()->save('groupDescription', 'wcf.acp.group.groupDescription'.$groupID, 'wcf.acp.group', 1);
				
			// update group name
			$groupEditor = new UserGroupEditor($returnValues['returnValues']);
			$groupEditor->update([
				'groupDescription' => 'wcf.acp.group.groupDescription'.$groupID
			]);
		}
		
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign([
			'success' => true
		]);
		
		// reset values
		$this->groupName = '';
		
		I18nHandler::getInstance()->reset();
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		$this->optionTree = $this->optionHandler->getOptionTree();
		if (empty($_POST)) {
			$this->activeTabMenuItem = $this->optionTree[0]['object']->categoryName;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables();
		
		WCF::getTPL()->assign([
			'groupName' => $this->groupName,
			'groupDescription' => $this->groupDescription,
			'optionTree' => $this->optionTree,
			'action' => 'add',
			'priority' => $this->priority,
			'userOnlineMarking' => $this->userOnlineMarking,
			'showOnTeamPage' => $this->showOnTeamPage
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function show() {
		// check master password
		WCFACP::checkMasterPassword();
		
		// show form
		parent::show();
	}
}
