<?php
namespace wcf\system\category;
use wcf\data\category\Category;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\builder\CategoryCacheBuilder;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;

/**
 * Handles the categories.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Category
 */
class CategoryHandler extends SingletonFactory {
	/**
	 * cached categories
	 * @var	Category[]
	 */
	protected $categories = [];
	
	/**
	 * category ids grouped by the object type they belong to
	 * @var	integer[][]
	 */
	protected $objectTypeCategoryIDs = [];
	
	/**
	 * maps the names of the category object types to the object type ids
	 * @var	integer[]
	 */
	protected $objectTypeIDs = [];
	
	/**
	 * list of category object types
	 * @var	ObjectType[]
	 */
	protected $objectTypes = [];
	
	/**
	 * Returns all category objects with the given object type. If no object
	 * type is given, all categories grouped by object type are returned.
	 * 
	 * @param	string		$objectType
	 * @return	Category[]|Category[][]
	 */
	public function getCategories($objectType = null) {
		$categories = [];
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
	 * Returns the category with the given id or `null` if no such category exists.
	 * 
	 * @param	integer		$categoryID
	 * @return	Category|null
	 */
	public function getCategory($categoryID) {
		if (isset($this->categories[$categoryID])) {
			return $this->categories[$categoryID];
		}
		
		return null;
	}
	
	/**
	 * Returns the child categories of the category with the given id.
	 * 
	 * The second parameter is only needed if $categoryID is 0.
	 * 
	 * @param	integer		$categoryID
	 * @param	integer		$objectTypeID
	 * @return	Category[]
	 * @throws	SystemException
	 */
	public function getChildCategories($categoryID, $objectTypeID = null) {
		if (!$categoryID && $objectTypeID === null) {
			throw new SystemException("Missing object type id");
		}
		
		$categories = [];
		foreach ($this->categories as $category) {
			if ($category->parentCategoryID == $categoryID && ($categoryID || $category->objectTypeID == $objectTypeID)) {
				$categories[$category->categoryID] = $category;
			}
		}
		
		return $categories;
	}
	
	/**
	 * Returns the category object type with the given id or `null` if no such object type exists.
	 * 
	 * @param	integer		$objectTypeID
	 * @return	ObjectType|null
	 */
	public function getObjectType($objectTypeID) {
		if (isset($this->objectTypeIDs[$objectTypeID])) {
			return $this->getObjectTypeByName($this->objectTypeIDs[$objectTypeID]);
		}
		
		return null;
	}
	
	/**
	 * Returns the category object type with the given name or `null` if no such object type exists.
	 * 
	 * @param	string		$objectType
	 * @return	ObjectType|null
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
	 * @return	ObjectType[]
	 */
	public function getObjectTypes() {
		return $this->objectTypes;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		$this->objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.category');
		foreach ($this->objectTypes as $objectType) {
			$this->objectTypeIDs[$objectType->objectTypeID] = $objectType->objectType;
		}
		
		$this->categories = CategoryCacheBuilder::getInstance()->getData([], 'categories');
		$this->objectTypeCategoryIDs = CategoryCacheBuilder::getInstance()->getData([], 'objectTypeCategoryIDs');
	}
	
	/**
	 * Reloads the category cache.
	 */
	public function reloadCache() {
		CategoryCacheBuilder::getInstance()->reset();
		
		$this->init();
	}
}
