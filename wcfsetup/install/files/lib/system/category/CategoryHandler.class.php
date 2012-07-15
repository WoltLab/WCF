<?php
namespace wcf\system\category;
use wcf\data\category\Category;
use wcf\data\category\CategoryEditor;
use wcf\system\SingletonFactory;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\CacheHandler;

/**
 * Handles categories.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.category
 * @category 	Community Framework
 */
class CategoryHandler extends SingletonFactory {
	/**
	 * cached categories
	 * @var	array<wcf\data\category\Category>
	 */
	protected $categories = array();
	
	/**
	 * maps each category id to its object type id and object type category id
	 * @var	array<array>
	 */
	protected $categoryIDs = array();
	
	/**
	 * mapes the names of the category object types to the object type ids
	 * @var	array<integer>
	 */
	protected $objectTypeIDs = array();
	
	/**
	 * list of category object types
	 * @var	array<wcf\data\object\type>
	 */
	protected $objectTypes = array();
	
	/**
	 * Returns all category objects of the given object type.
	 * 
	 * @param	integer		$objectType
	 * @return	array<wcf\data\category\Category>
	 */
	public function getCategories($objectType) {
		if (isset($this->categories[$objectType])) {
			return $this->categories[$objectType];
		}
		
		return array();
	}
	
	/**
	 * Returns the category object of the given object type and object type
	 * category id.
	 * 
	 * @param	string		$objectType
	 * @param	integer		$objectTypeCategoryID
	 * @return	wcf\data\category\Category
	 */
	public function getCategory($objectType, $objectTypeCategoryID) {
		if (isset($this->categories[$objectType][$objectTypeCategoryID])) {
			return $this->categories[$objectType][$objectTypeCategoryID];
		}
		
		return null;
	}
	
	/**
	 * Returns the category object with the given category id.
	 * 
	 * @param	integer		$categoryID
	 * @return	wcf\data\category\Category
	 */
	public function getCategoryByID($categoryID) {
		if (isset($this->categoryIDs[$categoryID])) {
			return $this->getCategory($this->categoryIDs[$categoryID]['objectType'], $this->categoryIDs[$categoryID]['objectTypeCategoryID']);
		}
		
		return null;
	}
	
	/**
	 * Returns the child categories of the given category.
	 * 
	 * @param	wcf\data\category\Category	$category
	 * @return	array<wcf\data\category\Category>
	 */
	public function getChildCategories(Category $category) {
		$categories = array();
		
		$objectType = $this->getObjectType($category->objectTypeID)->objectType;
		if (isset($this->categories[$objectType])) {
			foreach ($this->categories[$objectType] as $__category) {
				if ($__category->parentCategoryID == $category->objectTypeCategoryID) {
					$categories[$__category->objectTypeCategoryID] = $__category;
				}
			}
		}
		
		return $categories;
	}
	
	/**
	 * Gets the object type with the given id.
	 * 
	 * @param	integer 	$objectTypeID
	 * @return	wcf\data\object\type\ObjectType
	 */
	public function getObjectType($objectTypeID) {
		if (isset($this->objectTypeIDs[$objectTypeID])) {
			return $this->getObjectTypeByName($this->objectTypeIDs[$objectTypeID]);
		}
		
		return null;
	}
	
	/**
	 * Gets the object type with the given name.
	 * 
	 * @param	string 		$objectType
	 * @return	wcf\data\object\type\ObjectType
	 */
	public function getObjectTypeByName($objectType) {
		if (isset($this->objectTypes[$objectType])) {
			return $this->objectTypes[$objectType];
		}
		
		return null;
	}
	
	/**
	 * @see	wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		$this->objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.category');
		foreach ($this->objectTypes as $objectType) {
			$this->objectTypeIDs[$objectType->objectTypeID] = $objectType->objectType;
		}
		
		$cacheName = 'category-'.PACKAGE_ID;
		CacheHandler::getInstance()->addResource(
			$cacheName,
			WCF_DIR.'cache/cache.'.$cacheName.'.php',
			'wcf\system\cache\builder\CategoryCacheBuilder'
		);
		$this->categories = CacheHandler::getInstance()->get($cacheName, 'categories');
		$this->categoryIDs = CacheHandler::getInstance()->get($cacheName, 'categoryIDs');
	}
	
	/**
	 * Reloads the category cache.
	 */
	public function reloadCache() {
		CacheHandler::getInstance()->clearResource('category-'.PACKAGE_ID);
		
		$this->init();
	}
}
