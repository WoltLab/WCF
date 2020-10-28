<?php
namespace wcf\acp\form;
use wcf\data\custom\option\CustomOption;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nValue;
use wcf\system\WCF;

/**
 * Default implementation for custom options utilizing the option system.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 * @since       3.1
 */
abstract class AbstractCustomOptionForm extends AbstractAcpForm {
	/**
	 * option name
	 * @var	string
	 */
	public $optionTitle = '';
	
	/**
	 * option description
	 * @var	string
	 */
	public $optionDescription = '';
	
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
	 * show order
	 * @var	integer
	 */
	public $showOrder = 0;
	
	/**
	 * 1 if the option is disabled
	 * @var	boolean
	 * @since	5.2
	 */
	public $isDisabled = 0;
	
	/**
	 * action class name
	 * @var string
	 */
	public $actionClass = '';
	
	/**
	 * base class name
	 * @var string
	 */
	public $baseClass = '';
	
	/**
	 * editor class name
	 * @var string
	 */
	public $editorClass = '';
	
	/**
	 * object instance
	 * @var CustomOption
	 */
	public $option;
	
	/**
	 * object id
	 * @var integer
	 */
	public $optionID;
	
	/**
	 * available option types
	 * @var	string[]
	 */
	public static $availableOptionTypes = [
		'boolean',
		'checkboxes',
		'date',
		'integer',
		'float',
		'multiSelect',
		'radioButton',
		'select',
		'text',
		'textarea',
		'URL'
	];
	
	/**
	 * list of option type that require select options
	 * @var	string[]
	 */
	public static $optionTypesUsingSelectOptions = [
		'checkboxes',
		'multiSelect',
		'radioButton',
		'select'
	];
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (empty($this->action)) {
			throw new \RuntimeException("The 'action' property must equal 'add' or 'edit'.");
		}
		
		if ($this->action === 'edit') {
			if (isset($_REQUEST['id'])) $this->optionID = intval($_REQUEST['id']);
			$this->option = new $this->baseClass($this->optionID);
			if (!$this->option->getObjectID()) {
				throw new IllegalLinkException();
			}
		}
		
		$this->registerI18nValue(new I18nValue('optionTitle'));
		
		$optionDescription = new I18nValue('optionDescription');
		$optionDescription->setFlags(I18nValue::ALLOW_EMPTY);
		$this->registerI18nValue($optionDescription);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['optionType'])) $this->optionType = $_POST['optionType'];
		if (isset($_POST['defaultValue'])) $this->defaultValue = $_POST['defaultValue'];
		if (isset($_POST['validationPattern'])) $this->validationPattern = $_POST['validationPattern'];
		if (isset($_POST['selectOptions'])) $this->selectOptions = $_POST['selectOptions'];
		if (isset($_POST['required'])) $this->required = intval($_POST['required']);
		if (isset($_POST['showOrder'])) $this->showOrder = intval($_POST['showOrder']);
		if (isset($_POST['isDisabled'])) $this->isDisabled = 1;
		
		if ($this->optionType == 'boolean' || $this->optionType == 'integer') {
			$this->defaultValue = intval($this->defaultValue);
			
			if ($this->optionType == 'boolean') $this->validationPattern = '';
		}
		if ($this->optionType == 'float') {
			$this->defaultValue = floatval($this->defaultValue);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		// option type
		if (!in_array($this->optionType, self::$availableOptionTypes)) {
			throw new UserInputException('optionType');
		}
		
		// select options
		if (in_array($this->optionType, self::$optionTypesUsingSelectOptions) && empty($this->selectOptions)) {
			throw new UserInputException('selectOptions');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		if ($this->action === 'edit' && empty($_POST)) {
			$this->readDataI18n($this->option);
			
			$this->optionType = $this->option->optionType;
			$this->defaultValue = $this->option->defaultValue;
			$this->validationPattern = $this->option->validationPattern;
			$this->selectOptions = $this->option->selectOptions;
			$this->required = $this->option->required;
			$this->showOrder = $this->option->showOrder;
			$this->isDisabled = $this->option->isDisabled;
		}
		
		parent::readData();
	}
	
	/**
	 * Returns the list of database values including additional fields.
	 * 
	 * @return      array
	 */
	protected function getDatabaseValues() {
		return array_merge($this->additionalFields, [
			'optionTitle' => $this->optionTitle,
			'optionDescription' => $this->optionDescription,
			'optionType' => $this->optionType,
			'defaultValue' => $this->defaultValue,
			'showOrder' => $this->showOrder,
			'isDisabled' => $this->isDisabled,
			'validationPattern' => $this->validationPattern,
			'selectOptions' => $this->selectOptions,
			'required' => $this->required
		]);
	}
	
	/**
	 * Returns additional parameters passed to the database object action object.
	 * 
	 * @since       5.4
	 */
	protected function getActionParameters(): array {
		return [];
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		if ($this->action === 'add') {
			$this->objectAction = new $this->actionClass([], 'create', array_merge(
				['data' => $this->getDatabaseValues()],
				$this->getActionParameters()
			));
			
			$this->saveI18n($this->objectAction->executeAction()['returnValues'], $this->editorClass);
			
			$this->reset();
		}
		else {
			$this->beforeSaveI18n($this->option);
			
			$this->objectAction = new $this->actionClass([$this->option], 'update', array_merge(
				['data' => $this->getDatabaseValues()],
				$this->getActionParameters()
			));
			$this->objectAction->executeAction();
			
			$this->saved();
			
			// show success message
			WCF::getTPL()->assign('success', true);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function reset() {
		parent::reset();
		
		// reset values
		$this->optionTitle = $this->optionDescription = $this->optionType = $this->defaultValue = $this->validationPattern = $this->selectOptions = '';
		$this->optionType = 'text';
		$this->required = $this->showOrder = $this->isDisabled = 0;
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		$variables = [
			'defaultValue' => $this->defaultValue,
			'validationPattern' => $this->validationPattern,
			'optionType' => $this->optionType,
			'selectOptions' => $this->selectOptions,
			'required' => $this->required,
			'showOrder' => $this->showOrder,
			'isDisabled' => $this->isDisabled,
			'action' => $this->action,
			'availableOptionTypes' => self::$availableOptionTypes,
			'optionTypesUsingSelectOptions' => self::$optionTypesUsingSelectOptions
		];
		
		if ($this->action === 'edit') {
			$variables['option'] = $this->option;
			$variables['optionID'] = $this->optionID;
		}
		
		WCF::getTPL()->assign($variables);
	}
}
