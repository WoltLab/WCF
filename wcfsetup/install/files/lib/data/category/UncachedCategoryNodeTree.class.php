<?php
namespace wcf\data\category;
use wcf\system\category\CategoryHandler;

/**
 * Represents an uncached tree of category nodes.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.category
 * @category	Community Framework
 */
class UncachedCategoryNodeTree extends CategoryNodeTree {
	/**
	 * locally cached categories
	 * @var	array<\wcf\data\category\Category>
	 */
	protected $categoryCache = array();
	
	/**
	 * locally cached category ids grouped by the id of their parent category
	 * @var	array
	 */
	protected $categoryStructureCache = array();
	
	/**
	 * @see	\wcf\data\category\CategoryNodeTree::buildTree()
	 */
	protected function buildTree() {
		$categoryList = new CategoryList();
		$categoryList->getConditionBuilder()->add('category.objectTypeID = ?', array(CategoryHandler::getInstance()->getObjectTypeByName($this->objectType)->objectTypeID));
		$categoryList->readObjects();
		foreach ($categoryList as $category) {
			if (!isset($this->categoryStructureCache[$category->parentCategoryID])) {
				$this->categoryStructureCache[$category->parentCategoryID] = array();
			}
			
			$this->categoryStructureCache[$category->parentCategoryID][] = $category->categoryID;
			$this->categoryCache[$category->categoryID] = $category;
		}
		
		parent::buildTree();
	}
	
	/**
	 * @see	\wcf\data\category\CategoryNodeTree::getCategory()
	 */
	protected function getCategory($categoryID) {
		return $this->categoryCache[$categoryID];
	}
	
	/**
	 * @see	\wcf\data\category\CategoryNodeTree::getChildCategories()
	 */
	protected function getChildCategories(CategoryNode $parentNode) {
		$categories = array();
		if (isset($this->categoryStructureCache[$parentNode->categoryID])) {
			foreach ($this->categoryStructureCache[$parentNode->categoryID] as $categoryID) {
				$categories[$categoryID] = $this->getCategory($categoryID);
			}
		}
		
		return $categories;
	}
}
