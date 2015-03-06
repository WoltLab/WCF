<?php
namespace wcf\data\category;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\category\CategoryHandler;
use wcf\system\exception\PermissionDeniedException;

/**
 * Abstract implementation of a decorated category.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.category
 * @category	Community Framework
 */
abstract class AbstractDecoratedCategory extends DatabaseObjectDecorator {
	/**
	 * list of all child categories of this category
	 * @var	array<\wcf\data\category\Category>
	 */
	protected $childCategories = null;
	
	/**
	 * list of all parent category generations of this category
	 * @var	array<\wcf\data\category\AbstractDecoratedCategory>
	 */
	protected $parentCategories = null;
	
	/**
	 * parent category of this category
	 * @var	\wcf\data\category\AbstractDecoratedCategory
	 */
	protected $parentCategory = null;
	
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\category\Category';
	
	/**
	 * @see	\wcf\data\IPermissionObject::checkPermissions()
	 */
	public function checkPermissions(array $permissions) {
		foreach ($permissions as $permission) {
			if (!$this->getPermission($permission)) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * @see	\wcf\data\category\Category::getChildCategories()
	 */
	public function getChildCategories() {
		if ($this->childCategories === null) {
			$this->childCategories = array();
			foreach ($this->getDecoratedObject()->getChildCategories() as $category) {
				$this->childCategories[$category->categoryID] = new static($category);
			}
		}
		
		return $this->childCategories;
	}
	
	/**
	 * @see	\wcf\data\category\Category::getParentCategories()
	 */
	public function getParentCategories() {
		if ($this->parentCategories === null) {
			$this->parentCategories = array();
			foreach ($this->getDecoratedObject()->getParentCategories() as $category) {
				$this->parentCategories[$category->categoryID] = new static($category);
			}
		}
		
		return $this->parentCategories;
	}
	
	/**
	 * @see	\wcf\data\category\Category::getParentCategory()
	 */
	public function getParentCategory() {
		if ($this->parentCategoryID && $this->parentCategory === null) {
			$this->parentCategory = new static($this->getDecoratedObject()->getParentCategory());
		}
		
		return $this->parentCategory;
	}
	
	/**
	 * @see	\wcf\data\category\Category::isParentCategory()
	 */
	public function isParentCategory(AbstractDecoratedCategory $category) {
		return $this->getDecoratedObject()->isParentCategory($category->getDecoratedObject());
	}
	
	/**
	 * Returns the decorated category with the given id or null if no such
	 * category exists.
	 * 
	 * @param	integer		$categoryID
	 * @return	\wcf\data\category\AbstractDecoratedCategory
	 */
	public static function getCategory($categoryID) {
		$category = CategoryHandler::getInstance()->getCategory($categoryID);
		if ($category) {
			return new static($category);
		}
		
		return null;
	}
}
