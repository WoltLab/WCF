<?php
namespace wcf\data\category;
use wcf\data\DatabaseObject;
use wcf\system\category\CategoryHandler;
use wcf\system\request\IRouteController;
use wcf\system\WCF;

/**
 * Represents a category.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.category
 * @category	Community Framework
 */
class Category extends DatabaseObject implements IRouteController {
	/**
	 * category type of this category
	 * @var	wcf\system\category\ICategoryType
	 */
	protected $categoryType = null;
	
	/**
	 * list of all parent category generations of this category
	 * @var	array<wcf\data\category\Category>
	 */
	protected $parentCategories = null;
	
	/**
	 * parent category of this category
	 * @var	wcf\data\category\Category
	 */
	protected $parentCategory = null;
	
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'categoryID';
	
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'category';
	
	/**
	 * @see	wcf\data\IStorableObject::__get()
	 */
	public function __get($name) {
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
	 * Returns the category type of this category.
	 * 
	 * @return	wcf\system\category\ICategoryType
	 */
	public function getCategoryType() {
		if ($this->categoryType === null) {
			$this->categoryType = CategoryHandler::getInstance()->getObjectType($this->objectTypeID)->getProcessor();
		}
		
		return $this->categoryType;
	}
	
	/**
	 * @see	wcf\system\request\IRouteController::getID()
	 */
	public function getID() {
		return $this->categoryID;
	}
	
	/**
	 * Returns the parent category of this category.
	 * 
	 * @return	wcf\data\category\Category
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
	 * @return	array<wcf\data\category\Category>
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
	 * @see	wcf\data\ITitledDatabaseObject::getTitle()
	 */
	public function getTitle() {
		return WCF::getLanguage()->get($this->title);
	}
	
	/**
	 * @see	wcf\data\DatabaseObject::handleData()
	 */
	protected function handleData($data) {
		// handle additional data
		$data['additionalData'] = @unserialize($data['additionalData']);
		if (!is_array($data['additionalData'])) {
			$data['additionalData'] = array();
		}
		
		parent::handleData($data);
	}
}
