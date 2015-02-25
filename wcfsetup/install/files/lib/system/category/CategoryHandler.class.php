<?php
namespace wcf\system\category;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\builder\CategoryCacheBuilder;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;

/**
 * Handles the categories.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.category
 * @category	Community Framework
 */
class CategoryHandler extends SingletonFactory {
	/**
	 * cached categories
	 * @var	array<\wcf\data\category\Category>
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
	 * @var	array<\wcf\data\object\type>
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
	 * @return	\wcf\data\category\Category
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
	 * @return	array<\wcf\data\category\Category>
	 */
	public function getChildCategories($categoryID, $objectTypeID = null) {
		if (!$categoryID && $objectTypeID === null) {
			throw new SystemException("Missing object type id");
		}
		
		$categories = array();
		foreach ($this->categories as $category) {
			if ($category->parentCategoryID == $categoryID && ($categoryID || $category->objectTypeID == $objectTypeID)) {
				$categories[$category->categoryID] = $category;
			}
		}
		
		return $categories;
	}
	
	/**
	 * Gets the object type with the given id.
	 * 
	 * @param	integer		$objectTypeID
	 * @return	\wcf\data\object\type\ObjectType
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
	 * @param	string		$objectType
	 * @return	\wcf\data\object\type\ObjectType
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
	 * @return	array<\wcf\data\object\type\ObjectType>
	 */
	public function getObjectTypes() {
		return $this->objectTypes;
	}
	
	/**
	 * @see	\wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		$this->objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.category');
		foreach ($this->objectTypes as $objectType) {
			$this->objectTypeIDs[$objectType->objectTypeID] = $objectType->objectType;
		}
		
		$this->categories = CategoryCacheBuilder::getInstance()->getData(array(), 'categories');
		$this->objectTypeCategoryIDs = CategoryCacheBuilder::getInstance()->getData(array(), 'objectTypeCategoryIDs');
	}
	
	/**
	 * Reloads the category cache.
	 */
	public function reloadCache() {
		CategoryCacheBuilder::getInstance()->reset();
		
		$this->init();
	}
}
