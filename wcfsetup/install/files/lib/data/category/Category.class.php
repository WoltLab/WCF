<?php
namespace wcf\data\category;
use wcf\data\user\User;
use wcf\data\IPermissionObject;
use wcf\data\ProcessibleDatabaseObject;
use wcf\system\category\CategoryHandler;
use wcf\system\category\CategoryPermissionHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\request\IRouteController;
use wcf\system\WCF;

/**
 * Represents a category.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.category
 * @category	Community Framework
 */
class Category extends ProcessibleDatabaseObject implements IPermissionObject, IRouteController {
	/**
	 * list of all child categories of this category
	 * @var	array<\wcf\data\category\Category>
	 */
	protected $childCategories = null;
	
	/**
	 * list of all parent category generations of this category
	 * @var	array<\wcf\data\category\Category>
	 */
	protected $parentCategories = null;
	
	/**
	 * parent category of this category
	 * @var	\wcf\data\category\Category
	 */
	protected $parentCategory = null;
	
	/**
	 * acl permissions of this category for the active user
	 * @deprecated
	 * @var	array<boolean>
	 */
	protected $permissions = null;
	
	/**
	 * acl permissions of this category grouped by the id of the user they
	 * belong to
	 * @var	array
	 */
	protected $userPermissions = array();
	
	/**
	 * fallback return value used in Category::getPermission()
	 * @var	boolean
	 */
	protected $defaultPermission = false;
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'categoryID';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'category';
	
	/**
	 * @see	\wcf\data\ProcessibleDatabaseObject::$processorInterface
	 */
	protected static $processorInterface = 'wcf\system\category\ICategoryType';
	
	/**
	 * @see	\wcf\data\IStorableObject::__get()
	 */
	public function __get($name) {
		// forward 'className' property requests to object type
		if ($name == 'className') {
			return $this->getObjectType()->className;
		}
		
		$value = parent::__get($name);
		
		// check additional data
		if ($value === null) {
			if (isset($this->data['additionalData'][$name])) {
				$value = $this->data['additionalData'][$name];
			}
		}
		
		return $value;
	}
	
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
	 * Returns the category object type of the category.
	 * 
	 * @return	\wcf\data\category\Category
	 */
	public function getObjectType() {
		return CategoryHandler::getInstance()->getObjectType($this->objectTypeID);
	}
	
	/**
	 * Returns the child categories of this category.
	 * 
	 * @return	array<\wcf\data\category\Category>
	 */
	public function getChildCategories() {
		if ($this->childCategories === null) {
			$this->childCategories = CategoryHandler::getInstance()->getChildCategories($this->categoryID);
		}
		
		return $this->childCategories;
	}
	
	/**
	 * Returns the parent category of this category.
	 * 
	 * @return	\wcf\data\category\Category
	 */
	public function getParentCategory() {
		if ($this->parentCategoryID && $this->parentCategory === null) {
			$this->parentCategory = CategoryHandler::getInstance()->getCategory($this->parentCategoryID);
		}
		
		return $this->parentCategory;
	}
	
	/**
	 * Returns the parent categories of this category.
	 * 
	 * @return	array<\wcf\data\category\Category>
	 */
	public function getParentCategories() {
		if ($this->parentCategories === null) {
			$this->parentCategories = array();
			$parentCaregory = $this;
			while ($parentCaregory = $parentCaregory->getParentCategory()) {
				$this->parentCategories[] = $parentCaregory;
			}
			
			$this->parentCategories = array_reverse($this->parentCategories);
		}
		
		return $this->parentCategories;
	}
	
	/**
	 * Returns true if given category is a parent category of this category.
	 * 
	 * @param	\wcf\data\category\Category	$category
	 * @return	boolean
	 */
	public function isParentCategory(Category $category) {
		return in_array($category, $this->getParentCategories());
	}
	
	/**
	 * @see	\wcf\data\IPermissionObject::getPermission()
	 */
	public function getPermission($permission, User $user = null) {
		if ($user === null) {
			$user = WCF::getUser();
		}
		
		if (!isset($this->userPermissions[$user->userID])) {
			$this->userPermissions[$user->userID] = CategoryPermissionHandler::getInstance()->getPermissions($this, $user);
			
			if ($user->userID == WCF::getUser()->userID) {
				$this->permissions = $this->userPermissions[$user->userID];
			}
		}
		
		if (isset($this->userPermissions[$user->userID][$permission])) {
			return $this->userPermissions[$user->userID][$permission];
		}
		
		if ($this->getParentCategory()) {
			return $this->getParentCategory()->getPermission($permission, $user);
		}
		
		if ($this->getObjectType()->defaultpermission !== null) {
			return $this->getObjectType()->defaultpermission ? true : false;
		}
		
		return $this->defaultPermission;
	}
	
	/**
	 * @see	\wcf\data\ITitledObject::getTitle()
	 */
	public function getTitle() {
		return WCF::getLanguage()->get($this->title);
	}
	
	/**
	 * Returns the description of this category.
	 * 
	 * @return	string
	 */
	public function getDescription() {
		if ($this->description) return WCF::getLanguage()->get($this->description);
		return '';
	}
	
	/**
	 * @see	\wcf\data\DatabaseObject::handleData()
	 */
	protected function handleData($data) {
		// handle additional data
		$data['additionalData'] = @unserialize($data['additionalData']);
		if (!is_array($data['additionalData'])) {
			$data['additionalData'] = array();
		}
		
		parent::handleData($data);
	}
	
	/**
	 * @see	\wcf\data\ITitledObject::getTitle()
	 */
	public function __toString() {
		return $this->getTitle();
	}
}
