<?php
namespace wcf\system\option;
use wcf\data\option\category\OptionCategory;
use wcf\data\option\Option;
use wcf\system\application\ApplicationHandler;
use wcf\system\cache\builder\OptionCacheBuilder;
use wcf\system\event\EventHandler;
use wcf\system\exception\ImplementationException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Handles options.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option
 */
class OptionHandler implements IOptionHandler {
	/**
	 * list of application abbreviations
	 * @var	string[]
	 */
	protected $abbreviations;
	
	/**
	 * cache class name
	 * @var	string
	 */
	protected $cacheClass = OptionCacheBuilder::class;
	
	/**
	 * list of all option categories
	 * @var	OptionCategory[]
	 */
	public $cachedCategories;
	
	/**
	 * list of all options
	 * @var	Option[]
	 */
	public $cachedOptions;
	
	/**
	 * category structure
	 * @var	array
	 */
	public $cachedCategoryStructure;
	
	/**
	 * option structure
	 * @var	array
	 */
	public $cachedOptionToCategories;
	
	/**
	 * name of the active option category
	 * @var	string
	 */
	public $categoryName = '';
	
	/**
	 * list of options and option categories that are only accessible for owners in enterprise
	 * mode
	 * this blacklist only applies to options, not other types of options like user options
	 *
	 * @var	string[][]
	 * @since	5.2
	 */
	protected $enterpriseBlacklist = [
		'categories' => [
			'general.cache',
			'general.mail.send',
			'general.page.seo',
			'general.system.cookie',
			'general.system.http',
			'general.system.image',
			'general.system.packageServer',
			'general.system.proxy',
			'general.system.search',
			'module.development',
			'security.general.secrets',
		],
		'options' => [
			'mail_from_address',
			'register_activation_method',
		]
	];
	
	/**
	 * options of the active category
	 * @var	Option[]
	 */
	public $options = [];
	
	/**
	 * type object cache
	 * @var	IOptionType[]
	 */
	public $typeObjects = [];
	
	/**
	 * language item pattern
	 * @var	string
	 */
	public $languageItemPattern = '';
	
	/**
	 * option values
	 * @var	mixed[]
	 */
	public $optionValues = [];
	
	/**
	 * raw option values
	 * @var	mixed[]
	 */
	public $rawValues = [];
	
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
	 * @inheritDoc
	 */
	public function __construct($supportI18n, $languageItemPattern = '', $categoryName = '') {
		$this->categoryName = $categoryName;
		$this->languageItemPattern = $languageItemPattern;
		$this->supportI18n = $supportI18n;
		
		// load cache on init
		$this->readCache();
	}
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function validate() {
		$errors = [];
		
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
	 * @inheritDoc
	 */
	public function getOptionTree($parentCategoryName = '', $level = 0) {
		$tree = [];
		
		if (isset($this->cachedCategoryStructure[$parentCategoryName])) {
			// get super categories
			foreach ($this->cachedCategoryStructure[$parentCategoryName] as $superCategoryName) {
				$superCategoryObject = $this->cachedCategories[$superCategoryName];
				$superCategory = [
					'object' => $superCategoryObject,
					'categories' => [],
					'options' => []
				];
				
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
	 * @inheritDoc
	 */
	public function getCategoryOptions($categoryName = '', $inherit = true) {
		$children = [];
		
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
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function save($categoryName = null, $optionPrefix = null) {
		$saveOptions = [];
		
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
		
		return [
			'object' => $option,
			'value' => isset($this->optionValues[$option->optionName]) ? $this->optionValues[$option->optionName] : null,
			'html' => $html,
			'cssClassName' => $this->getTypeObject($option->optionType)->getCSSClassName(),
			'hideLabelInSearch' => $this->getTypeObject($option->optionType)->hideLabelInSearch()
		];
	}
	
	/**
	 * Wrapper function to preserve backwards compatibility with the visibility of `getOption()`.
	 * 
	 * @param string $optionName
	 * @return array
	 * @since 5.2
	 */
	public function getSingleOption($optionName) {
		return $this->getOption($optionName);
	}
	
	/**
	 * Validates an option.
	 * 
	 * @param	Option		$option
	 * @throws	UserInputException
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
			if (!preg_match('~'.str_replace('~', '\~', $option->validationPattern).'~', $this->optionValues[$option->optionName])) {
				throw new UserInputException($option->optionName, 'validationFailed');
			}
		}
		
		// validate by type object
		$typeObj->validate($option, $newValue);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getFormElement($type, Option $option) {
		return $this->getTypeObject($type)->getFormElement($option, (isset($this->optionValues[$option->optionName]) ? $this->optionValues[$option->optionName] : null));
	}
	
	/**
	 * Returns an object of the requested option type.
	 * 
	 * @param	string			$type
	 * @return	IOptionType
	 * @throws	SystemException
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
	 * @throws	ImplementationException
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
				$this->abbreviations = [];
				
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
		
		if (!is_subclass_of($className, IOptionType::class)) {
			throw new ImplementationException($className, IOptionType::class);
		}
		
		return $className;
	}
	
	/**
	 * Fetches all options and option categories from cache.
	 */
	protected function readCache() {
		$cache = call_user_func([$this->cacheClass, 'getInstance']);
		
		// get cache contents
		$this->cachedCategories = $cache->getData([], 'categories');
		$this->cachedOptions = $cache->getData([], 'options');
		$this->cachedCategoryStructure = $cache->getData([], 'categoryStructure');
		$this->cachedOptionToCategories = $cache->getData([], 'optionToCategories');
		
		// allow option manipulation
		EventHandler::getInstance()->fireAction($this, 'afterReadCache');
	}
	
	/**
	 * @inheritDoc
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
	 * Removes any option that is not listed in the provided list.
	 * 
	 * @param string[] $optionNames
	 * @since 5.2
	 */
	public function filterOptions(array $optionNames) {
		$this->options = array_filter($this->options, function (Option $option) use ($optionNames) {
			return in_array($option->optionName, $optionNames);
		});
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
	 * @param	OptionCategory		$category
	 * @return	boolean
	 */
	protected function checkCategory(OptionCategory $category) {
		if (!$category->validateOptions() || !$category->validatePermissions()) {
			return false;
		}
		
		if (ENABLE_ENTERPRISE_MODE && !WCF::getUser()->hasOwnerAccess() && get_class($category) === OptionCategory::class) {
			return !in_array($category->categoryName, $this->enterpriseBlacklist['categories']);
		}
		
		return true;
	}
	
	/**
	 * Checks the required permissions and options of an option.
	 * 
	 * @param	Option		$option
	 * @return	boolean
	 */
	protected function checkOption(Option $option) {
		return $option->validateOptions() && $option->validatePermissions() && $this->checkVisibility($option);
	}
	
	/**
	 * Checks visibility of an option.
	 * 
	 * @param	Option		$option
	 * @return	boolean
	 */
	protected function checkVisibility(Option $option) {
		if (!$option->isVisible()) {
			return false;
		}
		
		if (ENABLE_ENTERPRISE_MODE && !WCF::getUser()->hasOwnerAccess() && get_class($option) === Option::class) {
			return !in_array($option->optionName, $this->enterpriseBlacklist['options']);
		}
		
		return true;
	}
}
