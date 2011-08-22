<?php
namespace wcf\acp\form;
use wcf\data\option\category\OptionCategory;
use wcf\data\option\Option;
use wcf\form\AbstractForm;
use wcf\system\cache\CacheHandler;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\util\ClassUtil;
use wcf\util\StringUtil;

/**
 * This class provides default implementations for a list of options.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
abstract class AbstractOptionListForm extends AbstractForm {
	/**
	 * @see wcf\form\AbstractForm::$errorField
	 */
	public $errorField = array();
	
	/**
	 * @see wcf\form\AbstractForm::$errorType
	 */
	public $errorType = array();

	/**
	 * cache name
	 * @var string
	 */
	public $cacheName = 'option-';
	
	/**
	 * cache class name
	 * @var string
	 */
	public $cacheClass = 'wcf\system\cache\builder\OptionCacheBuilder';

	/**
	 * list of all option categories
	 * @var array<wcf\data\option\category\OptionCategory>
	 */
	public $cachedCategories = array();
	
	/**
	 * list of all options
	 * @var array<wcf\data\option\Option>
	 */
	public $cachedOptions = array();
	
	/**
	 * category structure
	 * @var array
	 */
	public $cachedCategoryStructure = array();
	
	/**
	 * option structure
	 * @var array
	 */
	public $cachedOptionToCategories = array();
	
	/**
	 * raw option values
	 * @var array<mixed>
	 */
	public $rawValues = array();
	
	/**
	 * option values
	 * @var array<mixed>
	 */
	public $optionValues = array();
	
	/**
	 * Name of the active option category.
	 * @var string
	 */
	public $categoryName = '';
	
	/**
	 * Options of the active category.
	 * @var array<Option>
	 */
	public $options = array();
	
	/**
	 * Type object cache.
	 * @var array
	 */
	public $typeObjects = array();
		
	/**
	 * @see wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['values']) && is_array($_POST['values'])) $this->rawValues = $_POST['values'];
	}

	/**
	 * Returns an object of the requested option type.
	 * 
	 * @param	string			$type
	 * @return	wcf\system\option\IOptionType
	 */
	protected function getTypeObject($type) {
		if (!isset($this->typeObjects[$type])) {
			$className = 'wcf\system\option\\'.StringUtil::firstCharToUpperCase($type).'OptionType';
			
			// validate class
			if (!class_exists($className)) {
				throw new SystemException("unable to find class '".$className."'");
			}
			if (!ClassUtil::isInstanceOf($className, 'wcf\system\option\IOptionType')) {
				throw new SystemException("'".$className."' should implement wcf\system\option\IOptionType");
			}
			// create instance
			$this->typeObjects[$type] = new $className();
		}
		
		return $this->typeObjects[$type];
	}
	
	/**
	 * @see wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		foreach ($this->options as $option) {
			try {
				$this->validateOption($option);
			}
			catch (UserInputException $e) {
				$this->errorType[$e->getField()] = $e->getType();
			}
		}
		
		if (count($this->errorType) > 0) {
			throw new UserInputException('options', $this->errorType);
		}
	}
	
	/**
	 * Validates an option.
	 * 
	 * @param	Option		$option
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
	 * Gets all options and option categories from cache.
	 */
	protected function readCache() {
		// init cache
		$cacheName = $this->cacheName.PACKAGE_ID;
		CacheHandler::getInstance()->addResource($cacheName, WCF_DIR.'cache/cache.'.$cacheName.'.php', $this->cacheClass);
		
		// get cache contents
		$this->cachedCategories = CacheHandler::getInstance()->get($cacheName, 'categories');
		$this->cachedOptions = CacheHandler::getInstance()->get($cacheName, 'options');
		$this->cachedCategoryStructure = CacheHandler::getInstance()->get($cacheName, 'categoryStructure');
		$this->cachedOptionToCategories = CacheHandler::getInstance()->get($cacheName, 'optionToCategories');
		
		// get active options
		$this->loadActiveOptions($this->categoryName);
	}
	
	/**
	 * Creates a list of all active options.
	 * 
	 * @param	string		$parentCategoryName
	 */
	protected function loadActiveOptions($parentCategoryName) {
		if (!isset($this->cachedCategories[$parentCategoryName]) || static::checkCategory($this->cachedCategories[$parentCategoryName])) {
			if (isset($this->cachedOptionToCategories[$parentCategoryName])) {
				foreach ($this->cachedOptionToCategories[$parentCategoryName] as $optionName) {
					if (static::checkOption($this->cachedOptions[$optionName])) {
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
	protected static function checkCategory(OptionCategory $category) {
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
	 * @see wcf\system\option\IOptionType::getFormElement()
	 */
	protected function getFormElement($type, Option $option) {
		return $this->getTypeObject($type)->getFormElement($option, (isset($this->optionValues[$option->optionName]) ? $this->optionValues[$option->optionName] : null));
	}
	
	/**
	 * Checks the required permissions and options of an option.
	 * 
	 * @param	Option		$option
	 * @return	boolean
	 */
	protected static function checkOption(Option $option) {
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
			$options = explode(',', strtoupper($option->options));
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
	 * Returns the tree of options.
	 * 
	 * @param	string		$parentCategoryName
	 * @param	integer		$level
	 * @return	array
	 */
	protected function getOptionTree($parentCategoryName = '', $level = 0) {
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
				
				if (static::checkCategory($superCategoryObject)) {
					if ($level <= 1) {
						$superCategory['categories'] = $this->getOptionTree($superCategoryName, $level + 1);
					}
					if ($level > 1 || count($superCategory['categories']) == 0) {
						$superCategory['options'] = $this->getCategoryOptions($superCategoryName);
					}
					else {
						$superCategory['options'] = $this->getCategoryOptions($superCategoryName, false);
					}
					
					if (count($superCategory['categories']) > 0 || count($superCategory['options']) > 0) {
						$tree[] = $superCategory;
					}
				}
			}
		}
	
		return $tree;
	}
	
	/**
	 * Returns a list with the options of a specific option category.
	 * 
	 * @param	string		$categoryName
	 * @param	boolean		$inherit
	 * @return	array
	 */
	protected function getCategoryOptions($categoryName = '', $inherit = true) {
		$children = array();
		
		// get sub categories
		if ($inherit && isset($this->cachedCategoryStructure[$categoryName])) {
			foreach ($this->cachedCategoryStructure[$categoryName] as $subCategoryName) {
				$children = array_merge($children, $this->getCategoryOptions($subCategoryName));
			}
		}
		
		// get options
		if (isset($this->cachedOptionToCategories[$categoryName])) {
			$i = 0;
			$last = count($this->cachedOptionToCategories[$categoryName]) - 1;
			foreach ($this->cachedOptionToCategories[$categoryName] as $optionName) {
				if (!isset($this->options[$optionName]) || !$this->checkOption($this->options[$optionName])) continue;
				
				// get option object
				$option = $this->options[$optionName];
				
				// get form element html
				$html = $this->getFormElement($option->optionType, $option);
				
				// add option to list
				$children[] = array(
					'object' => $option,
					'html' => $html,
					'cssClassName' => $this->getTypeObject($option->optionType)->getCSSClassName()
				);
				
				$i++;
			}
		}
		
		return $children;
	}
}
