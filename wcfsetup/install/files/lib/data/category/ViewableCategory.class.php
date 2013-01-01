<?php
namespace wcf\data\category;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\IPermissionObject;
use wcf\system\category\CategoryPermissionHandler;
use wcf\system\exception\PermissionDeniedException;

/**
 * Represents a viewable category.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.category
 * @category	Community Framework
 */
class ViewableCategory extends DatabaseObjectDecorator implements IPermissionObject {
	/**
	 * list of all parent category generations of this category
	 * @var	array<wcf\data\category\ViewableCategory>
	 */
	protected $parentCategories = null;
	
	/**
	 * parent category of this category
	 * @var	wcf\data\category\ViewableCategory
	 */
	protected $parentCategory = null;
	
	/**
	 * @see	wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\category\Category';
	
	/**
	 * acl permissions for the active user of this category
	 * @var	array<boolean>
	 */
	protected $permissions = null;
	
	/**
	 * @see	wcf\data\IPermissionObject::checkPermissions()
	 */
	public function checkPermissions(array $permissions) {
		foreach ($permissions as $permission) {
			if (!$this->getPermission($permission)) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * @see	wcf\data\category\Category::getParentCategories()
	 */
	public function getParentCategories() {
		if ($this->parentCategories === null) {
			$this->parentCategories = array();
			$className = get_class($this);
			
			foreach ($this->getDecoratedObject()->getParentCategories() as $category) {
				$this->parentCategories[$category->categoryID] = new $className($category);
			}
		}
		
		return $this->parentCategories;
	}
	
	/**
	 * @see	wcf\data\category\Category::getParentCategory()
	 */
	public function getParentCategory() {
		if ($this->parentCategoryID && $this->parentCategory === null) {
			$className = get_class($this);
			
			$this->parentCategory = new $className($this->getDecoratedObject()->getParentCategory());
		}
		
		return $this->parentCategory;
	}
	
	/**
	 * @see	wcf\data\IPermissionObject::getPermission()
	 */
	public function getPermission($permission) {
		if ($this->permissions === null) {
			$this->permissions = CategoryPermissionHandler::getInstance()->getPermissions($this->getDecoratedObject());
		}
		
		if (isset($this->permissions[$permission])) {
			return $this->permissions[$permission];
		}
		
		return false;
	}
}
