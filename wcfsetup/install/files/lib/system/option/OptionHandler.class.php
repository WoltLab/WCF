<?php
namespace wcf\system\option;
use wcf\data\option\category\OptionCategory;
use wcf\data\option\Option;
use wcf\system\application\ApplicationHandler;
use wcf\system\event\EventHandler;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;
use wcf\util\ClassUtil;
use wcf\util\StringUtil;

/**
 * Handles options.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category	Community Framework
 */
class OptionHandler implements IOptionHandler {
	/**
	 * list of application abbreviations
	 * @var	array<string>
	 */
	protected $abbreviations = null;
	
	/**
	 * cache class name
	 * @var	string
	 */
	protected $cacheClass = 'wcf\system\cache\builder\OptionCacheBuilder';
	
	/**
	 * list of all option categories
	 * @var	array<\wcf\data\option\category\OptionCategory>
	 */
	public $cachedCategories = null;
	
	/**
	 * list of all options
	 * @var	array<\wcf\data\option\Option>
	 */
	public $cachedOptions = null;
	
	/**
	 * category structure
	 * @var	array
	 */
	public $cachedCategoryStructure = null;
	
	/**
	 * option structure
	 * @var	array
	 */
	public $cachedOptionToCategories = null;
	
	/**
	 * name of the active option category
	 * @var	string
	 */
	public $categoryName = '';
	
	/**
	 * options of the active category
	 * @var	array<Option>
	 */
	public $options = array();
	
	/**
	 * type object cache
	 * @var	array<\wcf\system\option\IOptionType>
	 */
	public $typeObjects = array();
	
	/**
	 * language item pattern
	 * @var	string
	 */
	public $languageItemPattern = '';
	
	/**
	 * option values
	 * @var	array<mixed>
	 */
	public $optionValues = array();
	
	/**
	 * raw option values
	 * @var	array<mixed>
	 */
	public $rawValues = array();
	
	/**
	 * true, if options support i18n
	 * @var	boolean
	 */
	public $supportI18n = false;
	
	/**
	 * cache initialization state
	 * @var	boolean
	 */
	public $didInit = false;
	
	/**
	 * @see	\wcf\system\option\IOptionHandler::__construct()
	 */
	public function __construct($supportI18n, $languageItemPattern = '', $categoryName = '') {
		$this->categoryName = $categoryName;
		$this->languageItemPattern = $languageItemPattern;
		$this->supportI18n = $supportI18n;
		
		// load cache on init
		$this->readCache();
	}
	
	/**
	 * @see	\wcf\system\option\IOptionHandler::readUserInput()
	 */
	public function readUserInput(array &$source) {
		if (isset($source['values']) && is_array($source['values'])) $this->rawValues = $source['values'];
		
		if ($this->supportI18n) {
			foreach ($this->options as $option) {
				if ($option->supportI18n) {
					I18nHandler::getInstance()->register($option->optionName);
					I18nHandler::getInstance()->setOptions($option->optionName, $option->packageID, $option->optionValue, $this->languageItemPattern);
				}
			}
			I18nHandler::getInstance()->readValues();
		}
	}
	
	/**
	 * @see	\wcf\system\option\IOptionHandler::validate()
	 */
	public function validate() {
		$errors = array();
		
		foreach ($this->options as $option) {
			try {
				$this->validateOption($option);
			}
			catch (UserInputException $e) {
				$errors[$e->getField()] = $e->getType();
			}
		}
		
		return $errors;
	}
	
	/**
	 * @see	\wcf\system\option\IOptionHandler::getOptionTree()
	 */
	public function getOptionTree($parentCategoryName = '', $level = 0) {
		$tree = array();
		
		if (isset($this->cachedCategoryStructure[$parentCategoryName])) {
			// get super categories
			foreach ($this->cachedCategoryStructure[$parentCategoryName] as $superCategoryName) {
				$superCategoryObject = $this->cachedCategories[$superCategoryName];
				$superCategory = array(
					'object' => $superCategoryObject,
					'categories' => array(),
					'options' => array()
				);
				
				if ($this->checkCategory($superCategoryObject)) {
					if ($level <= 1) {
						$superCategory['categories'] = $this->getOptionTree($superCategoryName, $level + 1);
					}
					
					if ($level > 1 || empty($superCategory['categories'])) {
						$superCategory['options'] = $this->getCategoryOptions($superCategoryName);
					}
					else {
						$superCategory['options'] = $this->getCategoryOptions($superCategoryName, false);
					}
					
					if (!empty($superCategory['categories']) || !empty($superCategory['options'])) {
						$tree[] = $superCategory;
					}
				}
			}
		}
		
		return $tree;
	}
	
	/**
	 * @see	\wcf\system\option\IOptionHandler::getCategoryOptions()
	 */
	public function getCategoryOptions($categoryName = '', $inherit = true) {
		$children = array();
		
		// get sub categories
		if ($inherit && isset($this->cachedCategoryStructure[$categoryName])) {
			foreach ($this->cachedCategoryStructure[$categoryName] as $subCategoryName) {
				$children = array_merge($children, $this->getCategoryOptions($subCategoryName));
			}
		}
		
		// get options
		if (isset($this->cachedOptionToCategories[$categoryName])) {
			foreach ($this->cachedOptionToCategories[$categoryName] as $optionName) {
				if (!isset($this->options[$optionName]) || !$this->checkOption($this->options[$optionName])) continue;
				
				// add option to list
				$option = $this->getOption($optionName);
				if ($option !== null) {
					$children[] = $option;
				}
			}
		}
		
		return $children;
	}
	
	/**
	 * @see	\wcf\system\option\IOptionHandler::readData()
	 */
	public function readData() {
		foreach ($this->options as $option) {
			if ($this->supportI18n && $option->supportI18n) {
				I18nHandler::getInstance()->register($option->optionName);
				I18nHandler::getInstance()->setOptions($option->optionName, $option->packageID, $option->optionValue, $this->languageItemPattern);
			}
			
			$this->optionValues[$option->optionName] = $option->optionValue;
		}
	}
	
	/**
	 * @see	\wcf\system\option\IOptionHandler::save()
	 */
	public function save($categoryName = null, $optionPrefix = null) {
		$saveOptions = array();
		
		if ($this->supportI18n && ($categoryName === null || $optionPrefix === null)) {
			throw new SystemException("category name or option prefix missing");
		}
		
		foreach ($this->options as $option) {
			// handle i18n support
			if ($this->supportI18n && $option->supportI18n) {
				if (I18nHandler::getInstance()->isPlainValue($option->optionName)) {
					I18nHandler::getInstance()->remove($optionPrefix . $option->optionID);
					$saveOptions[$option->optionID] = I18nHandler::getInstance()->getValue($option->optionName);
				}
				else {
					I18nHandler::getInstance()->save($option->optionName, $optionPrefix . $option->optionID, $categoryName, $option->packageID);
					$saveOptions[$option->optionID] = $optionPrefix . $option->optionID;
				}
			}
			else {
				$saveOptions[$option->optionID] = $this->optionValues[$option->optionName];
			}
		}
		
		return $saveOptions;
	}
	
	/**
	 * Returns a parsed option.
	 * 
	 * @param	string		$optionName
	 * @return	array
	 */
	protected function getOption($optionName) {
		// get option object
		$option = $this->options[$optionName];
		
		// get form element html
		$html = $this->getFormElement($option->optionType, $option);
		
		return array(
			'object' => $option,
			'html' => $html,
			'cssClassName' => $this->getTypeObject($option->optionType)->getCSSClassName(),
			'hideLabelInSearch' => $this->getTypeObject($option->optionType)->hideLabelInSearch()
		);
	}
	
	/**
	 * Validates an option.
	 * 
	 * @param	\wcf\data\option\Option		$option
	 */
	protected function validateOption(Option $option) {
		// get type object
		$typeObj = $this->getTypeObject($option->optionType);
		
		// get new value
		$newValue = isset($this->rawValues[$option->optionName]) ? $this->rawValues[$option->optionName] : null;
		
		// get save value
		$this->optionValues[$option->optionName] = $typeObj->getData($option, $newValue);
		
		// validate with pattern
		if ($option->validationPattern) {
			if (!preg_match('~'.$option->validationPattern.'~', $this->optionValues[$option->optionName])) {
				throw new UserInputException($option->optionName, 'validationFailed');
			}
		}
		
		// validate by type object
		$typeObj->validate($option, $newValue);
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::getFormElement()
	 */
	protected function getFormElement($type, Option $option) {
		return $this->getTypeObject($type)->getFormElement($option, (isset($this->optionValues[$option->optionName]) ? $this->optionValues[$option->optionName] : null));
	}
	
	/**
	 * Returns an object of the requested option type.
	 * 
	 * @param	string			$type
	 * @return	\wcf\system\option\IOptionType
	 */
	public function getTypeObject($type) {
		if (!isset($this->typeObjects[$type])) {
			$className = $this->getClassName($type);
			if ($className === null) {
				throw new SystemException("unable to find class for option type '".$type."'");
			}
			
			// create instance
			$this->typeObjects[$type] = new $className();
		}
		
		return $this->typeObjects[$type];
	}
	
	/**
	 * Returns class name for option type.
	 * 
	 * @param	string		$optionType
	 * @return	string
	 */
	protected function getClassName($optionType) {
		$optionType = StringUtil::firstCharToUpperCase($optionType);
		
		// attempt to validate against WCF first
		$isValid = false;
		$className = 'wcf\system\option\\'.$optionType.'OptionType';
		if (class_exists($className)) {
			$isValid = true;
		}
		else {
			if ($this->abbreviations === null) {
				$this->abbreviations = array();
				
				$applications = ApplicationHandler::getInstance()->getApplications();
				foreach ($applications as $application) {
					$this->abbreviations[] = ApplicationHandler::getInstance()->getAbbreviation($application->packageID);
				}
			}
			
			foreach ($this->abbreviations as $abbreviation) {
				$className = $abbreviation.'\system\option\\'.$optionType.'OptionType';
				if (class_exists($className)) {
					$isValid = true;
					break;
				}
			}
		}
		
		// validate class
		if (!$isValid) {
			return null;
		}
		
		if (!ClassUtil::isInstanceOf($className, 'wcf\system\option\IOptionType')) {
			throw new SystemException("'".$className."' does not implement 'wcf\system\option\IOptionType'");
		}
		
		return $className;
	}
	
	/**
	 * Gets all options and option categories from cache.
	 */
	protected function readCache() {
		$cache = call_user_func(array($this->cacheClass, 'getInstance'));
		
		// get cache contents
		$this->cachedCategories = $cache->getData(array(), 'categories');
		$this->cachedOptions = $cache->getData(array(), 'options');
		$this->cachedCategoryStructure = $cache->getData(array(), 'categoryStructure');
		$this->cachedOptionToCategories = $cache->getData(array(), 'optionToCategories');
		
		// allow option manipulation
		EventHandler::getInstance()->fireAction($this, 'afterReadCache');
	}
	
	/**
	 * Initializes active options.
	 */
	public function init() {
		if (!$this->didInit) {
			// get active options
			$this->loadActiveOptions($this->categoryName);
			
			// mark options as initialized
			$this->didInit = true;
		}
	}
	
	/**
	 * Creates a list of all active options.
	 * 
	 * @param	string		$parentCategoryName
	 */
	protected function loadActiveOptions($parentCategoryName) {
		if (!isset($this->cachedCategories[$parentCategoryName]) || $this->checkCategory($this->cachedCategories[$parentCategoryName])) {
			if (isset($this->cachedOptionToCategories[$parentCategoryName])) {
				foreach ($this->cachedOptionToCategories[$parentCategoryName] as $optionName) {
					if ($this->checkOption($this->cachedOptions[$optionName])) {
						$this->options[$optionName] = $this->cachedOptions[$optionName];
					}
				}
			}
			
			if (isset($this->cachedCategoryStructure[$parentCategoryName])) {
				foreach ($this->cachedCategoryStructure[$parentCategoryName] as $categoryName) {
					$this->loadActiveOptions($categoryName);
				}
			}
		}
	}
	
	/**
	 * Checks the required permissions and options of a category.
	 * 
	 * @param	\wcf\data\option\category\OptionCategory		$category
	 * @return	boolean
	 */
	protected function checkCategory(OptionCategory $category) {
		if ($category->permissions) {
			$hasPermission = false;
			$permissions = explode(',', $category->permissions);
			foreach ($permissions as $permission) {
				if (WCF::getSession()->getPermission($permission)) {
					$hasPermission = true;
					break;
				}
			}
			
			if (!$hasPermission) return false;
			
		}
		
		if ($category->options) {
			$hasEnabledOption = false;
			$options = explode(',', strtoupper($category->options));
			foreach ($options as $option) {
				if (defined($option) && constant($option)) {
					$hasEnabledOption = true;
					break;
				}
			}
			
			if (!$hasEnabledOption) return false;
		}
		
		return true;
	}
	
	/**
	 * Checks the required permissions and options of an option.
	 * 
	 * @param	\wcf\data\option\Option		$option
	 * @return	boolean
	 */
	protected function checkOption(Option $option) {
		if ($option->permissions) {
			$hasPermission = false;
			$permissions = explode(',', $option->permissions);
			foreach ($permissions as $permission) {
				if (WCF::getSession()->getPermission($permission)) {
					$hasPermission = true;
					break;
				}
			}
			
			if (!$hasPermission) return false;
			
		}
		
		if ($option->options) {
			$hasEnabledOption = false;
			$__options = explode(',', strtoupper($option->options));
			foreach ($__options as $__option) {
				if (defined($__option) && constant($__option)) {
					$hasEnabledOption = true;
					break;
				}
			}
			
			if (!$hasEnabledOption) return false;
		}
		
		if (!$this->checkVisibility($option)) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Checks visibility of an option.
	 * 
	 * @param	\wcf\data\option\Option		$option
	 * @return	boolean
	 */
	protected function checkVisibility(Option $option) {
		return $option->isVisible();
	}
}
