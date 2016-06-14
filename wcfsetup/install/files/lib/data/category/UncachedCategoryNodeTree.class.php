<?php
namespace wcf\data\category;
use wcf\system\category\CategoryHandler;

/**
 * Represents an uncached tree of category nodes.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Category
 */
class UncachedCategoryNodeTree extends CategoryNodeTree {
	/**
	 * locally cached categories
	 * @var	Category[]
	 */
	protected $categoryCache = [];
	
	/**
	 * locally cached category ids grouped by the id of their parent category
	 * @var	array
	 */
	protected $categoryStructureCache = [];
	
	/**
	 * @inheritDoc
	 */
	protected function buildTree() {
		$categoryList = new CategoryList();
		$categoryList->getConditionBuilder()->add('category.objectTypeID = ?', [CategoryHandler::getInstance()->getObjectTypeByName($this->objectType)->objectTypeID]);
		$categoryList->sqlOrderBy = "category.showOrder ASC";
		$categoryList->readObjects();
		foreach ($categoryList as $category) {
			if (!isset($this->categoryStructureCache[$category->parentCategoryID])) {
				$this->categoryStructureCache[$category->parentCategoryID] = [];
			}
			
			$this->categoryStructureCache[$category->parentCategoryID][] = $category->categoryID;
			$this->categoryCache[$category->categoryID] = $category;
		}
		
		parent::buildTree();
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getCategory($categoryID) {
		return $this->categoryCache[$categoryID];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getChildCategories(CategoryNode $parentNode) {
		$categories = [];
		if (isset($this->categoryStructureCache[$parentNode->categoryID])) {
			foreach ($this->categoryStructureCache[$parentNode->categoryID] as $categoryID) {
				$categories[$categoryID] = $this->getCategory($categoryID);
			}
		}
		
		return $categories;
	}
}
