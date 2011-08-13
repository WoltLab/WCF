<?php
namespace wcf\acp\form;
use wcf\system\menu\acp\ACPMenu;
use wcf\data\user\group\UserGroup;
use wcf\data\user\group\UserGroupAction;
use wcf\data\user\group\UserGroupEditor;
use wcf\system\exception\UserInputException;
use wcf\system\exception\SystemException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\system\WCFACP;
use wcf\util\ArrayUtil;
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
	 * group identifier
	 * @var string
	 */
	public $groupIdentifier = '';
	
	/**
	 * group name if different languages
	 * @var	array<string>
	 */
	public $groupName = array();
	
	/**
	 * array with available language codes (language ids as key)
	 * @var	array<string>
	 */
	public $languageCodes = array();
	
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
		
		if (isset($_POST['groupIdentifier'])) {
			$this->groupIdentifier = StringUtil::trim($_POST['groupIdentifier']);
		}
		if (isset($_POST['groupName']) && is_array($_POST['groupName'])) {
			$this->groupName = ArrayUtil::trim($_POST['groupName']);
		}
		if (isset($_POST['activeTabMenuItem'])) {
			$this->activeTabMenuItem = $_POST['activeTabMenuItem'];
		}
		if (isset($_POST['activeSubTabMenuItem'])) {
			$this->activeSubTabMenuItem = $_POST['activeSubTabMenuItem'];
		}
	}
	
	/**
	 * @see wcf\form\IForm::validate()
	 */
	public function validate() {
		// validate dynamic options
		parent::validate();
		
		// validate group identifier
		try {
			$this->validateGroupIdentifier();
		}
		catch (UserInputException $e) {
			$this->errorType[$e->getField()] = $e->getType();
		}
		
		// validate group names
		foreach ($this->languageCodes as $languageID => $languageCode) {
			if (!isset($this->groupName[$languageID]) || empty($this->groupName[$languageID])) {
				$this->errorType['groupName'][$languageID] = 'empty';
			}
		}
	
		if (count($this->errorType) > 0) {
			throw new UserInputException('groupIdentifier', $this->errorType);
		}		
	}
	
	/**
	 * Validates the group identifier.
	 */
	protected function validateGroupIdentifier() {
		if (empty($this->groupIdentifier)) {
			throw new UserInputException('groupIdentifier');
		}
		else if (!preg_match('~wcf\.userGroup\.identifier\.(\w+)~', $this->groupIdentifier)) {
			throw new UserInputException('groupIdentifier', 'notValid');
		}
		else if (!UserGroupEditor::isAvailableGroupIdentifier($this->groupIdentifier)) {
			throw new UserInputException('groupIdentifier', 'notUnique');
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
			'data' => array_merge($this->additionalFields, array('groupIdentifier' => $this->groupIdentifier, 'groupName' => $this->groupName)),
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
		$this->groupIdentifier = '';
		$this->groupName = array();
		$this->optionValues = array();
	}
	
	/**
	 * @see wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get language codes
		$this->languageCodes = LanguageFactory::getLanguageCodes();
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
			'groupIdentifier' => $this->groupIdentifier,
			'optionTree' => $this->optionTree,
			'action' => 'add',
			'activeTabMenuItem' => $this->activeTabMenuItem,
			'activeSubTabMenuItem' => $this->activeSubTabMenuItem,
			'languageCodes' => $this->languageCodes,
			'groupName' => $this->groupName
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
	 * @see wcf\acp\form\AbstractOptionListForm::getTypeObject()
	 */
	protected function getTypeObject($type) {
		if (!isset($this->typeObjects[$type])) {
			$className = 'wcf\system\option\group\GroupOptionType'.ucfirst($type);
			
			// create instance
			if (!class_exists($className)) {
				throw new SystemException("unable to find class '".$className."'");
			}
			if (!ClassUtil::isInstanceOf($className, 'wcf\system\option\group\IGroupOptionType')) {
				throw new SystemException("'".$className."' should implement wcf\system\option\group\IGroupOptionType");
			}
			$this->typeObjects[$type] = new $className();
		}
		
		return $this->typeObjects[$type];
	}
}
