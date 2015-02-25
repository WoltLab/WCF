<?php
namespace wcf\system\search\acp;
use wcf\system\exception\SystemException;
use wcf\util\ClassUtil;

/**
 * Abstract implementation of a ACP search result provider with nested categories.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.search.acp
 * @category	Community Framework
 */
abstract class AbstractCategorizedACPSearchResultProvider extends AbstractACPSearchResultProvider {
	/**
	 * list of categories
	 * @var	array<\wcf\data\DatabaseObject>
	 */
	protected $categories = array();
	
	/**
	 * class name for category list
	 * @var	string
	 */
	protected $listClassName = '';
	
	/**
	 * list of top category names (level 1 and 2)
	 * @var	array<string>
	 */
	protected $topCategories = array();
	
	/**
	 * Creates a new categorized ACP search result provider.
	 */
	public function __construct() {
		$this->loadCategories();
	}
	
	/**
	 * Returns a level 1 or 2 category id for given category name.
	 * 
	 * @param	string		$categoryName
	 * @return	integer
	 */
	protected function getCategoryID($categoryName) {
		$category = $this->getTopCategory($categoryName);
		
		return $category->categoryID;
	}
	
	/**
	 * Returns a level 1 or 2 category name for given category name.
	 * 
	 * @param	string		$categoryName
	 * @return	string
	 */
	protected function getCategoryName($categoryName) {
		$category = $this->getTopCategory($categoryName);
		
		return $category->categoryName;
	}
	
	/**
	 * Returns a level 1 or 2 category for given category name.
	 * 
	 * @param	string			$categoryName
	 * @return	\wcf\data\DatabaseObject
	 */
	protected function getTopCategory($categoryName) {
		if (!$this->isValid($categoryName)) {
			throw new SystemException("Category name '".$categoryName."' is unknown");
		}
		
		// this is a top category
		if (in_array($categoryName, $this->topCategories)) {
			return $this->categories[$categoryName];
		}
		
		// check parent category
		return $this->getTopCategory($this->categories[$categoryName]->parentCategoryName);
	}
	
	/**
	 * Loads categories.
	 */
	protected function loadCategories() {
		// validate list class name
		if (empty($this->listClassName) || !ClassUtil::isInstanceOf($this->listClassName, 'wcf\data\DatabaseObjectList')) {
			throw new SystemException("Given class '".$this->listClassName."' is empty or invalid");
		}
		
		// read categories
		$categoryList = new $this->listClassName();
		$categoryList->readObjects();
		
		foreach ($categoryList as $category) {
			// validate options and permissions
			if (!$this->validate($category)) {
				continue;
			}
			
			// save level 1 categories
			if ($category->parentCategoryName == '') {
				$this->topCategories[] = $category->categoryName;
			}
			
			$this->categories[$category->categoryName] = $category;
		}
		
		// create level 2 categories
		$topCategories = array();
		foreach ($this->categories as $category) {
			if ($category->parentCategoryName && in_array($category->parentCategoryName, $this->topCategories)) {
				$topCategories[] = $category->categoryName;
			}
		}
		
		$this->topCategories = array_merge($this->topCategories, $topCategories);
	}
	
	/**
	 * Returns true if given category is valid and accessible.
	 * 
	 * @param	string		$categoryName
	 * @return	boolean
	 */
	protected function isValid($categoryName) {
		return isset($this->categories[$categoryName]);
	}
}
