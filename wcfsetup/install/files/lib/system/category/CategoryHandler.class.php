<?php
namespace wcf\system\category;
use wcf\data\category\Category;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\SingletonFactory;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\CacheHandler;
use wcf\system\exception\SystemException;

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
	 * category ids grouped by the object type they belong to
	 * @var	array<array>
	 */
	protected $objectTypeCategoryIDs = array();
	
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
	 * Returns all category objects with the given object type. If no object
	 * type is given, all categories grouped by object type are returned.
	 * 
	 * @param	string		$objectType
	 * @return	array<mixed>
	 */
	public function getCategories($objectType = null) {
		$categories = array();
		if ($objectType === null) {
			foreach ($this->objectTypes as $objectType) {
				$categories[$objectType->objectType] = $this->getCategories($objectType->objectType);
			}
		}
		else if (isset($this->objectTypeCategoryIDs[$objectType])) {
			foreach ($this->objectTypeCategoryIDs[$objectType] as $categoryID) {
				$categories[$categoryID] = $this->getCategory($categoryID);
			}
		}
		
		return $categories;
	}
	
	/**
	 * Returns the category object with the given category id.
	 * 
	 * @param	integer		$categoryID
	 * @return	wcf\data\category\Category
	 */
	public function getCategory($categoryID) {
		if (isset($this->categories[$categoryID])) {
			return $this->categories[$categoryID];
		}
		
		return null;
	}
	
	/**
	 * Returns the child categories of the given category.
	 * 
	 * @param	wcf\data\DatabaseObject		$category
	 * @return	array<wcf\data\category\Category>
	 */
	public function getChildCategories(DatabaseObject $category) {
		if (!($category instanceof Category) && (!($category instanceof DatabaseObjectDecorator) || !($category->getDecoratedObject() instanceof Category))) {
			throw new SystemException("Invalid object given (class: ".get_class($category).")");
		}
		
		$categories = array();
		foreach ($this->categories as $__category) {
			if ($__category->parentCategoryID == $category->categoryID && ($category->categoryID || $category->objectTypeID == $__category->objectTypeID)) {
				$categories[$__category->categoryID] = $__category;
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
	 * Returns all category object types.
	 * 
	 * @return	array<wcf\data\object\type\ObjectType>
	 */
	public function getObjectTypes() {
		return $this->objectTypes;
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
		$this->objectTypeCategoryIDs = CacheHandler::getInstance()->get($cacheName, 'objectTypeCategoryIDs');
	}
	
	/**
	 * Reloads the category cache.
	 */
	public function reloadCache() {
		CacheHandler::getInstance()->clearResource('category-'.PACKAGE_ID);
		
		$this->init();
	}
}
