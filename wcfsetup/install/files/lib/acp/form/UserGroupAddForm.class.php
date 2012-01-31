<?php
namespace wcf\acp\form;
use wcf\data\user\group\UserGroup;
use wcf\data\user\group\UserGroupAction;
use wcf\data\user\group\UserGroupEditor;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\menu\acp\ACPMenu;
use wcf\system\WCF;
use wcf\system\WCFACP;
use wcf\util\ClassUtil;
use wcf\util\StringUtil;

/**
 * Shows the group add form.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class UserGroupAddForm extends AbstractOptionListForm {
	/**
	 * @see wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.user.canAddGroup');
	
	/**
	 * @see wcf\page\AbstractPage::$templateName
	 */
	public $templateName = 'userGroupAdd';
	
	/**
	 * name of the active acp menu item
	 * @var string
	 */
	public $menuItemName = 'wcf.acp.menu.link.group.add';
	
	/**
	 * @see wcf\acp\form\AbstractOptionListForm::$cacheName
	 */
	public $cacheName = 'userGroup-option';
	
	/**
	 * active tab menu item name
	 * @var string
	 */
	public $activeTabMenuItem = '';
	
	/**
	 * active sub tab menu item name
	 * @var string
	 */
	public $activeMenuItem = '';
	
	/**
	 * the option tree
	 * @var array
	 */
	public $optionTree = array();
	
	/**
	 * @see	wcf\acp\form\AbstractOptionListForm::$optionHandlerClassName
	 */
	public $optionHandlerClassName = 'wcf\system\option\user\group\UserGroupOptionHandler';
	
	/**
	 * @see	wcf\acp\form\AbstractOptionListForm::$supportI18n
	 */
	public $supportI18n = false;
	
	/**
	 * group name
	 * @var string
	 */
	public $groupName = '';
	
	/**
	 * additional fields
	 * @var array
	 */
	public $additionalFields = array();
	
	/**
	 * list of values of group 'Anyone'
	 * @var	array
	 */
	public $defaultValues = array();
	
	/**
	 * @see	wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		I18nHandler::getInstance()->register('groupName');
	}
	
	/**
	 * @see wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		I18nHandler::getInstance()->readValues();
		
		if (I18nHandler::getInstance()->isPlainValue('groupName')) $this->groupName = I18nHandler::getInstance()->getValue('groupName');
		if (isset($_POST['activeTabMenuItem'])) $this->activeTabMenuItem = $_POST['activeTabMenuItem'];
		if (isset($_POST['activeMenuItem'])) $this->activeMenuItem = $_POST['activeMenuItem'];
	}
	
	/**
	 * @see wcf\form\IForm::validate()
	 */
	public function validate() {
		// validate dynamic options
		parent::validate();
		
		// validate group name
		try {
			if (!I18nHandler::getInstance()->validateValue('groupName')) {
				throw new UserInputException('groupName');
			}
		}
		catch (UserInputException $e) {
			$this->errorType[$e->getField()] = $e->getType();
		}
	
		if (count($this->errorType) > 0) {
			throw new UserInputException('groupName', $this->errorType);
		}		
	}
	
	/**
	 * @see wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// get default group
		$defaultGroup = UserGroup::getGroupByType(UserGroup::EVERYONE);
		$optionValues = $this->optionHandler->save();
		$saveOptions = array();
		foreach ($this->optionHandler->getCategoryOptions() as $option) {
			$option = $option['object'];
			if ($optionValues[$option->optionID] != $defaultGroup->getGroupOption($option->optionName)) {
				$saveOptions[$option->optionID] = $optionValues[$option->optionID];
			}
		}
		
		$data = array(
			'data' => array_merge($this->additionalFields, array('groupName' => $this->groupName)),
			'options' => $saveOptions
		);
		$groupAction = new UserGroupAction(array(), 'create', $data);
		$groupAction->executeAction();
		
		if (!I18nHandler::getInstance()->isPlainValue('groupName')) {
			$returnValues = $groupAction->getReturnValues();
			$groupID = $returnValues['returnValues']->groupID;
			I18nHandler::getInstance()->save('groupName', 'wcf.acp.group.group'.$groupID, 'wcf.acp.group', 1);
			
			// update group name
			$groupEditor = new UserGroupEditor($returnValues['returnValues']);
			$groupEditor->update(array(
				'groupName' => 'wcf.acp.group.group'.$groupID
			));
		}
		
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign(array(
			'success' => true
		));
		
		// reset values
		$this->groupName = '';
		$this->optionValues = array();
	}
	
	/**
	 * @see wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->optionTree = $this->optionHandler->getOptionTree();
		if (!count($_POST)) {
			$this->activeTabMenuItem = $this->optionTree[0]['object']->categoryName;
		}
	}
	
	/**
	 * @see wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables();
		
		WCF::getTPL()->assign(array(
			'groupName' => $this->groupName,
			'optionTree' => $this->optionTree,
			'action' => 'add',
			'activeTabMenuItem' => $this->activeTabMenuItem,
			'activeMenuItem' => $this->activeMenuItem
		));
	}

	/**
	 * @see wcf\form\IForm::show()
	 */
	public function show() {
		// set active menu item
		ACPMenu::getInstance()->setActiveMenuItem($this->menuItemName);
		
		// check master password
		WCFACP::checkMasterPassword();
		
		// show form
		parent::show();
	}
	
	/**
	 * @see wcf\acp\form\AbstractOptionListForm::getTypeObject()
	 */
	protected function getTypeObject($type) {
		if (!isset($this->typeObjects[$type])) {
			$className = 'wcf\system\option\user\group\\'.StringUtil::firstCharToUpperCase($type).'UserGroupOptionType';
			
			// create instance
			if (!class_exists($className)) {
				throw new SystemException("unable to find class '".$className."'");
			}
			if (!ClassUtil::isInstanceOf($className, 'wcf\system\option\user\group\IUserGroupOptionType')) {
				throw new SystemException("'".$className."' should implement wcf\system\option\user\group\IUserGroupOptionType");
			}
			$this->typeObjects[$type] = new $className();
		}
		
		return $this->typeObjects[$type];
	}
}
