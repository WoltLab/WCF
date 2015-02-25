<?php
namespace wcf\acp\form;
use wcf\data\user\option\category\UserOptionCategoryList;
use wcf\data\user\option\UserOptionAction;
use wcf\data\user\option\UserOptionEditor;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the user option add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class UserOptionAddForm extends AbstractForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.user.option.add';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.user.canManageUserOption');
	
	/**
	 * option name
	 * @var	string
	 */
	public $optionName = '';
	
	/**
	 * option description
	 * @var	string
	 */
	public $optionDescription = '';
	
	/**
	 * category name
	 * @var	string
	 */
	public $categoryName = '';
	
	/**
	 * option type
	 * @var	string
	 */
	public $optionType = 'text';
	
	/**
	 * option default value
	 * @var	string
	 */
	public $defaultValue = '';
	
	/**
	 * validation pattern
	 * @var	string
	 */
	public $validationPattern = '';
	
	/**
	 * select options
	 * @var	string
	 */
	public $selectOptions = '';
	
	/**
	 * field is required
	 * @var	boolean
	 */
	public $required = 0;
	
	/**
	 * shows this field in the registration process
	 * @var	boolean
	 */
	public $askDuringRegistration = 0;
	
	/**
	 * edit permission bitmask
	 * @var	integer
	 */
	public $editable = 3;
	
	/**
	 * view permission bitmask
	 * @var	integer
	 */
	public $visible = 15;
	
	/**
	 * field is searchable
	 * @var	boolean
	 */
	public $searchable = 0;
	
	/**
	 * show order
	 * @var	integer
	 */
	public $showOrder = 0;
	
	/**
	 * output class
	 * @var	string
	 */
	public $outputClass = '';
	
	/**
	 * available option categories
	 * @var	array<\wcf\data\user\option\UserOptionCategory>
	 */
	public $availableCategories = array();
	
	/**
	 * available option types
	 * @var	array<string>
	 */
	public static $availableOptionTypes = array(
		'aboutMe',
		'birthday',
		'boolean',
		'checkboxes',
		'date',
		'integer',
		'float',
		'password',
		'multiSelect',
		'radioButton',
		'select',
		'text',
		'textarea',
		'message',
		'URL'
	);
	
	/**
	 * list of option type that require select options
	 * @var	array<string>
	 */
	public static $optionTypesUsingSelectOptions = array(
		'checkboxes',
		'multiSelect',
		'radioButton',
		'select'
	);
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		I18nHandler::getInstance()->register('optionName');
		I18nHandler::getInstance()->register('optionDescription');
		
		// get available categories
		$categoryList = new UserOptionCategoryList();
		$categoryList->getConditionBuilder()->add('parentCategoryName = ?', array('profile'));
		$categoryList->readObjects();
		$this->availableCategories = $categoryList->getObjects();
	}
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		I18nHandler::getInstance()->readValues();
		
		if (I18nHandler::getInstance()->isPlainValue('optionName')) $this->optionName = I18nHandler::getInstance()->getValue('optionName');
		if (I18nHandler::getInstance()->isPlainValue('optionDescription')) $this->optionDescription = I18nHandler::getInstance()->getValue('optionDescription');
		if (isset($_POST['categoryName'])) $this->categoryName = $_POST['categoryName'];
		if (isset($_POST['optionType'])) $this->optionType = $_POST['optionType'];
		if (isset($_POST['defaultValue'])) $this->defaultValue = $_POST['defaultValue'];
		if (isset($_POST['validationPattern'])) $this->validationPattern = $_POST['validationPattern'];
		if (isset($_POST['selectOptions'])) $this->selectOptions = $_POST['selectOptions'];
		if (isset($_POST['required'])) $this->required = intval($_POST['required']);
		if (isset($_POST['askDuringRegistration'])) $this->askDuringRegistration = intval($_POST['askDuringRegistration']);
		if (isset($_POST['editable'])) $this->editable = intval($_POST['editable']);
		if (isset($_POST['visible'])) $this->visible = intval($_POST['visible']);
		if (isset($_POST['searchable'])) $this->searchable = intval($_POST['searchable']);
		if (isset($_POST['showOrder'])) $this->showOrder = intval($_POST['showOrder']);
		if (isset($_POST['outputClass'])) $this->outputClass = StringUtil::trim($_POST['outputClass']);
		
		if ($this->optionType == 'boolean' || $this->optionType == 'integer') {
			$this->defaultValue = intval($this->defaultValue);
		}
		if ($this->optionType == 'float') {
			$this->defaultValue = floatval($this->defaultValue);
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		// option name
		if (!I18nHandler::getInstance()->validateValue('optionName', true)) {
			throw new UserInputException('optionName', 'multilingual');
		}
		
		// category name
		if (empty($this->categoryName)) {
			throw new UserInputException('categoryName');
		}
		$sql = "SELECT	categoryID
			FROM	wcf".WCF_N."_user_option_category
			WHERE	categoryName = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->categoryName));
		if ($statement->fetchArray() === false) {
			throw new UserInputException('categoryName');
		}
		
		// option type
		if (!in_array($this->optionType, self::$availableOptionTypes)) {
			throw new UserInputException('optionType');
		}
		
		// select options
		if (in_array($this->optionType, self::$optionTypesUsingSelectOptions) && empty($this->selectOptions)) {
			throw new UserInputException('selectOptions');
		}
		
		if ($this->outputClass && !class_exists($this->outputClass)) {
			throw new UserInputException('outputClass', 'doesNotExist');
		}
		
		if ($this->editable < 1 || $this->editable > 3) {
			$this->editable = 3;
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		$this->objectAction = new UserOptionAction(array(), 'create', array('data' => array_merge($this->additionalFields, array(
			'optionName' => StringUtil::getRandomID(),
			'categoryName' => $this->categoryName,
			'optionType' => $this->optionType,
			'defaultValue' => $this->defaultValue,
			'showOrder' => $this->showOrder,
			'outputClass' => $this->outputClass,
			'validationPattern' => $this->validationPattern,
			'selectOptions' => $this->selectOptions,
			'required' => $this->required,
			'askDuringRegistration' => $this->askDuringRegistration,
			'searchable' => $this->searchable,
			'editable' => $this->editable,
			'visible' => $this->visible,
			'packageID' => 1,
			'additionalData' => ($this->optionType == 'select' ? serialize(array('allowEmptyValue' => true)) : '')
		))));
		$this->objectAction->executeAction();
		
		$returnValues = $this->objectAction->getReturnValues();
		$userOption = $returnValues['returnValues'];
		
		// save language vars
		I18nHandler::getInstance()->save('optionName', 'wcf.user.option.option'.$userOption->optionID, 'wcf.user.option');
		I18nHandler::getInstance()->save('optionDescription', 'wcf.user.option.option'.$userOption->optionID.'.description', 'wcf.user.option');
		$editor = new UserOptionEditor($userOption);
		$editor->update(array(
			'optionName' => 'option'.$userOption->optionID
		));
		$this->saved();
		
		// reset values
		$this->optionName = $this->optionDescription = $this->categoryName = $this->optionType = $this->defaultValue = $this->validationPattern = $this->selectOptions = $this->outputClass = '';
		$this->optionType = 'text';
		$this->required = $this->searchable = $this->showOrder = $this->askDuringRegistration = 0;
		$this->editable = 3;
		$this->visible = 15;
		
		I18nHandler::getInstance()->reset();
		
		// show success
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables();
		
		WCF::getTPL()->assign(array(
			'optionName' => $this->optionName,
			'optionDescription' => $this->optionDescription,
			'categoryName' => $this->categoryName,
			'optionType' => $this->optionType,
			'defaultValue' => $this->defaultValue,
			'validationPattern' => $this->validationPattern,
			'optionType' => $this->optionType,
			'selectOptions' => $this->selectOptions,
			'required' => $this->required,
			'askDuringRegistration' => $this->askDuringRegistration,
			'editable' => $this->editable,
			'visible' => $this->visible,
			'searchable' => $this->searchable,
			'showOrder' => $this->showOrder,
			'outputClass' => $this->outputClass,
			'action' => 'add',
			'availableCategories' => $this->availableCategories,
			'availableOptionTypes' => self::$availableOptionTypes
		));
	}
}
