<?php
namespace wcf\data\category;
use wcf\system\category\CategoryHandler;
use wcf\system\exception\SystemException;

/**
 * Represents a tree of category nodes.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.category
 * @category	Community Framework
 */
class CategoryNodeTree implements \IteratorAggregate {
	/**
	 * name of the category node class
	 * @var	string
	 */
	protected $nodeClassName = 'wcf\data\category\CategoryNode';
	
	/**
	 * id of the parent category
	 * @var	integer
	 */
	protected $parentCategoryID = 0;
	
	/**
	 * parent category node
	 * @var	\wcf\data\category\CategoryNode
	 */
	protected $parentNode = null;
	
	/**
	 * Creates a new instance of CategoryNodeTree.
	 * 
	 * @param	string			$objectType
	 * @param	integer			$parentCategoryID
	 * @param	boolean			$includeDisabledCategories
	 * @param	array<integer>		$excludedCategoryIDs
	 */
	public function __construct($objectType, $parentCategoryID = 0, $includeDisabledCategories = false, array $excludedCategoryIDs = array()) {
		$this->objectType = $objectType;
		$this->parentCategoryID = $parentCategoryID;
		$this->includeDisabledCategories = $includeDisabledCategories;
		$this->excludedCategoryIDs = $excludedCategoryIDs;
		
		// validate category object type
		if (CategoryHandler::getInstance()->getObjectTypeByName($this->objectType) === null) {
			throw new SystemException("Unknown category object type '".$this->objectType."'");
		}
	}
	
	/**
	 * Builds the category node tree.
	 */
	protected function buildTree() {
		$this->parentNode = $this->getNode($this->parentCategoryID);
		$this->buildTreeLevel($this->parentNode);
	}
	
	/**
	 * Builds a certain level of the tree.
	 * 
	 * @param	\wcf\data\category\CategoryNode	$parentNode
	 */
	protected function buildTreeLevel(CategoryNode $parentNode) {
		foreach ($this->getChildCategories($parentNode) as $childCategory) {
			$childNode = $this->getNode($childCategory->categoryID);
			
			if ($this->isIncluded($childNode)) {
				$parentNode->addChild($childNode);
				
				// build next level
				$this->buildTreeLevel($childNode);
			}
		}
	}
	
	/**
	 * Returns the category with the given id.
	 * 
	 * @param	integer		$categoryID
	 * @return	\wcf\data\category\Category
	 */
	protected function getCategory($categoryID) {
		return CategoryHandler::getInstance()->getCategory($categoryID);
	}
	
	/**
	 * Returns the child categories of the given category node.
	 * 
	 * @param	\wcf\data\category\CategoryNode		$parentNode
	 * @return	array<\wcf\data\category\Category>
	 */
	protected function getChildCategories(CategoryNode $parentNode) {
		return CategoryHandler::getInstance()->getChildCategories($parentNode->categoryID, $parentNode->objectTypeID);
	}
	
	/**
	 * @see	\IteratorAggregate::getIterator()
	 */
	public function getIterator() {
		if ($this->parentNode === null) {
			$this->buildTree();
		}
		
		return new \RecursiveIteratorIterator($this->parentNode, \RecursiveIteratorIterator::SELF_FIRST);
	}
	
	/**
	 * Returns the category node for the category with the given id.
	 * 
	 * @param	integer		$categoryID
	 * @return	\wcf\data\category\CategoryNode
	 */
	protected function getNode($categoryID) {
		if (!$categoryID) {
			$category = new Category(null, array(
				'categoryID' => 0,
				'objectTypeID' => CategoryHandler::getInstance()->getObjectTypeByName($this->objectType)->objectTypeID
			));
		}
		else {
			$category = $this->getCategory($categoryID);
		}
		
		// decorate category if necessary
		$decoratorClassName = call_user_func(array($this->nodeClassName, 'getBaseClass'));
		if ($decoratorClassName != 'wcf\data\category\Category') {
			$category = new $decoratorClassName($category);
		}
		
		return new $this->nodeClassName($category);
	}
	
	/**
	 * Returns true if the given category node fulfils all relevant conditions
	 * to be included in this tree.
	 * 
	 * @param	\wcf\data\category\CategoryNode		$categoryNode
	 * @return	boolean
	 */
	protected function isIncluded(CategoryNode $categoryNode) {
		return (!$categoryNode->isDisabled || $this->includeDisabledCategories) && !in_array($categoryNode->categoryID, $this->excludedCategoryIDs);
	}
}
