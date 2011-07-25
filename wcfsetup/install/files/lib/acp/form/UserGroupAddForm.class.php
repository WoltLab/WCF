<?php
namespace wcf\acp\form;
use wcf\system\menu\acp\ACPMenu;
use wcf\data\user\group\UserGroup;
use wcf\data\user\group\UserGroupAction;
use wcf\system\exception\UserInputException;
use wcf\system\exception\SystemException;
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
	 * @see AbstractOptionListForm::$cacheName
	 */
	public $cacheName = 'user_group-option-';
	
	/**
	 * active tab menu item name
	 * @var string
	 */
	public $activeTabMenuItem = '';
	
	/**
	 * active sub tab menu item name
	 * @var string
	 */
	public $activeSubTabMenuItem = '';
	
	/**
	 * the option tree
	 * @var array
	 */
	public $optionTree = array();
	
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
	 * @see wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['groupName'])) $this->groupName = StringUtil::trim($_POST['groupName']);
		if (isset($_POST['activeTabMenuItem'])) $this->activeTabMenuItem = $_POST['activeTabMenuItem'];
		if (isset($_POST['activeSubTabMenuItem'])) $this->activeSubTabMenuItem = $_POST['activeSubTabMenuItem'];
	}
	
	/**
	 * @see wcf\form\IForm::validate()
	 */
	public function validate() {
		// validate dynamic options
		parent::validate();
		
		// validate group name
		try {
			if (empty($this->groupName)) {
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
		$saveOptions = array();
		foreach ($this->options as $option) {
			if ($this->optionValues[$option->optionName] != $defaultGroup->getGroupOption($option->optionName)) {
				$saveOptions[$option->optionID] = $this->optionValues[$option->optionName];
			}
		}
		
		$data = array(
			'data' => array_merge($this->additionalFields, array('groupName' => $this->groupName)),
			'options' => $saveOptions
		);
		$groupAction = new UserGroupAction(array(), 'create', $data);
		$groupAction->executeAction();
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
		AbstractOptionListForm::readData();
		
		$this->optionTree = $this->getOptionTree();
		if (!count($_POST)) {
			$this->activeTabMenuItem = $this->optionTree[0]['object']->categoryName;
		}
	}
	
	/**
	 * @see wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'groupName' => $this->groupName,
			'optionTree' => $this->optionTree,
			'action' => 'add',
			'activeTabMenuItem' => $this->activeTabMenuItem,
			'activeSubTabMenuItem' => $this->activeSubTabMenuItem
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
		
		// get user options and categories from cache
		$this->readCache();
		
		// show form
		parent::show();
	}
	
	/**
	 * @see AbstractOptionListForm::getTypeObject()
	 */
	protected function getTypeObject($type) {
		if (!isset($this->typeObjects[$type])) {
			$className = 'wcf\system\option\group\GroupOptionType'.ucfirst($type);
			
			// create instance
			if (!class_exists($className)) {
				throw new SystemException("unable to find class '".$className."'", 11001);
			}
			if (!ClassUtil::isInstanceOf($className, 'wcf\system\option\group\IGroupOptionType')) {
				throw new SystemException("'".$className."' should implement wcf\system\option\group\IGroupOptionType", 11001);
			}
			$this->typeObjects[$type] = new $className();
		}
		
		return $this->typeObjects[$type];
	}
}
