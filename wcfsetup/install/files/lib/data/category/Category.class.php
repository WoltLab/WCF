<?php
namespace wcf\data\category;
use wcf\data\object\type\ObjectType;
use wcf\data\user\User;
use wcf\data\IPermissionObject;
use wcf\data\ProcessibleDatabaseObject;
use wcf\system\category\CategoryHandler;
use wcf\system\category\CategoryPermissionHandler;
use wcf\system\category\ICategoryType;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\request\IRouteController;
use wcf\system\WCF;

/**
 * Represents a category.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Category
 * 
 * @property-read	integer		$categoryID
 * @property-read	integer		$objectTypeID
 * @property-read	integer		$parentCategoryID
 * @property-read	string		$title
 * @property-read	string		$description
 * @property-read	integer		$showOrder
 * @property-read	integer		$time
 * @property-read	integer		$isDisabled
 * @property-read	array		$additionalData
 */
class Category extends ProcessibleDatabaseObject implements IPermissionObject, IRouteController {
	/**
	 * list of all child categories of this category
	 * @var	Category[]
	 */
	protected $childCategories = null;
	
	/**
	 * list of all parent category generations of this category
	 * @var	Category[]
	 */
	protected $parentCategories = null;
	
	/**
	 * parent category of this category
	 * @var	Category
	 */
	protected $parentCategory = null;
	
	/**
	 * acl permissions of this category for the active user
	 * @deprecated
	 * @var	boolean[]
	 */
	protected $permissions = null;
	
	/**
	 * acl permissions of this category grouped by the id of the user they
	 * belong to
	 * @var	array
	 */
	protected $userPermissions = [];
	
	/**
	 * fallback return value used in Category::getPermission()
	 * @var	boolean
	 */
	protected $defaultPermission = false;
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'categoryID';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'category';
	
	/**
	 * @inheritDoc
	 */
	protected static $processorInterface = ICategoryType::class;
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
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
	 * @return	ObjectType
	 */
	public function getObjectType() {
		return CategoryHandler::getInstance()->getObjectType($this->objectTypeID);
	}
	
	/**
	 * Returns the child categories of this category.
	 * 
	 * @return	Category[]
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
	 * @return	Category
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
	 * @return	Category[]
	 */
	public function getParentCategories() {
		if ($this->parentCategories === null) {
			$this->parentCategories = [];
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
	 * @param	Category	$category
	 * @return	boolean
	 */
	public function isParentCategory(Category $category) {
		return in_array($category, $this->getParentCategories());
	}
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	protected function handleData($data) {
		// handle additional data
		$data['additionalData'] = @unserialize($data['additionalData']);
		if (!is_array($data['additionalData'])) {
			$data['additionalData'] = [];
		}
		
		parent::handleData($data);
	}
	
	/**
	 * @inheritDoc
	 */
	public function __toString() {
		return $this->getTitle();
	}
}
