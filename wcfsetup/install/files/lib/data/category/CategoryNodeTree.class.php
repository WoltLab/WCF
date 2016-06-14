<?php
namespace wcf\data\category;
use wcf\system\category\CategoryHandler;
use wcf\system\exception\SystemException;

/**
 * Represents a tree of category nodes.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Category
 */
class CategoryNodeTree implements \IteratorAggregate {
	/**
	 * list of ids of categories which will not be inluded in the node tree
	 * @var	integer[]
	 */
	protected $excludedCategoryIDs = [];
	
	/**
	 * if true, disabled categories are also included in the node tree
	 * @var	boolean
	 */
	protected $includeDisabledCategories = false;
	
	/**
	 * maximum depth considered when building the node tree
	 * @var	integer
	 */
	protected $maxDepth = -1;
	
	/**
	 * name of the category node class
	 * @var	string
	 */
	protected $nodeClassName = CategoryNode::class;
	
	/**
	 * id of the parent category
	 * @var	integer
	 */
	protected $parentCategoryID = 0;
	
	/**
	 * parent category node
	 * @var	CategoryNode
	 */
	protected $parentNode = null;
	
	/**
	 * name of the category object type
	 * @var	string
	 */
	protected $objectType = '';
	
	/**
	 * Creates a new instance of CategoryNodeTree.
	 * 
	 * @param	string			$objectType
	 * @param	integer			$parentCategoryID
	 * @param	boolean			$includeDisabledCategories
	 * @param	integer[]		$excludedCategoryIDs
	 * @throws	SystemException
	 */
	public function __construct($objectType, $parentCategoryID = 0, $includeDisabledCategories = false, array $excludedCategoryIDs = []) {
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
	 * Sets the maximum depth considered when building the node tree, defaults
	 * to -1 which equals infinite.
	 * 
	 * @param	integer		$maxDepth
	 */
	public function setMaxDepth($maxDepth) {
		$this->maxDepth = $maxDepth;
	}
	
	/**
	 * Builds the category node tree.
	 */
	protected function buildTree() {
		$this->parentNode = $this->getNode($this->parentCategoryID);
		$this->buildTreeLevel($this->parentNode, $this->maxDepth);
	}
	
	/**
	 * Builds a certain level of the tree.
	 * 
	 * @param	CategoryNode	$parentNode
	 * @param	integer		$depth
	 */
	protected function buildTreeLevel(CategoryNode $parentNode, $depth = 0) {
		if ($this->maxDepth != -1 && $depth < 0) {
			return;
		}
		
		foreach ($this->getChildCategories($parentNode) as $childCategory) {
			$childNode = $this->getNode($childCategory->categoryID);
			
			if ($this->isIncluded($childNode)) {
				$parentNode->addChild($childNode);
				
				// build next level
				$this->buildTreeLevel($childNode, $depth - 1);
			}
		}
	}
	
	/**
	 * Returns the category with the given id.
	 * 
	 * @param	integer		$categoryID
	 * @return	Category
	 */
	protected function getCategory($categoryID) {
		return CategoryHandler::getInstance()->getCategory($categoryID);
	}
	
	/**
	 * Returns the child categories of the given category node.
	 * 
	 * @param	CategoryNode		$parentNode
	 * @return	Category[]
	 */
	protected function getChildCategories(CategoryNode $parentNode) {
		return CategoryHandler::getInstance()->getChildCategories($parentNode->categoryID, $parentNode->objectTypeID);
	}
	
	/**
	 * @inheritDoc
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
	 * @return	CategoryNode
	 */
	protected function getNode($categoryID) {
		if (!$categoryID) {
			$category = new Category(null, [
				'categoryID' => 0,
				'objectTypeID' => CategoryHandler::getInstance()->getObjectTypeByName($this->objectType)->objectTypeID
			]);
		}
		else {
			$category = $this->getCategory($categoryID);
		}
		
		// decorate category if necessary
		$decoratorClassName = call_user_func([$this->nodeClassName, 'getBaseClass']);
		if ($decoratorClassName != Category::class) {
			$category = new $decoratorClassName($category);
		}
		
		return new $this->nodeClassName($category);
	}
	
	/**
	 * Returns true if the given category node fulfils all relevant conditions
	 * to be included in this tree.
	 * 
	 * @param	CategoryNode		$categoryNode
	 * @return	boolean
	 */
	protected function isIncluded(CategoryNode $categoryNode) {
		return (!$categoryNode->isDisabled || $this->includeDisabledCategories) && !in_array($categoryNode->categoryID, $this->excludedCategoryIDs);
	}
}
