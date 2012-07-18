<?php
namespace wcf\data\category;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\DatabaseObject;
use wcf\system\category\CategoryHandler;

/**
 * Represents a category node.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.category
 * @category 	Community Framework
 */
class CategoryNode extends DatabaseObjectDecorator implements \RecursiveIterator, \Countable {
	/**
	 * child category nodes
	 * @var	array<wcf\data\category\CategoryNode>
	 */
	protected $childCategories = array();
	
	/**
	 * indicates if disabled categories are included
	 * @var	integer
	 */
	protected $inludeDisabledCategories = false;
	
	/**
	 * list of object type category ids of excluded categories
	 * @var	array<integer>
	 */
	protected $excludedCategoryIDs = false;
	
	/**
	 * @see	wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\category\Category';
	
	/**
	 * @see	wcf\data\DatabaseObjectDecorator::__construct()
	 */
	public function __construct(DatabaseObject $object, $inludeDisabledCategories = false, array $excludedCategoryIDs = array()) {
		parent::__construct($object);
		
		$this->inludeDisabledCategories = $inludeDisabledCategories;
		$this->excludedCategoryIDs = $excludedCategoryIDs;
		
		$className = get_called_class();
		foreach (CategoryHandler::getInstance()->getChildCategories($this->getDecoratedObject()) as $category) {
			if ($this->fulfillsConditions($category)) {
				$this->childCategories[] = new $className($category, $inludeDisabledCategories, $excludedCategoryIDs);
			}
		}
	}
	
	/**
	 * @see	\Countable::count()
	 */
	public function count() {
		return count($this->childCategories);
	}
	
	/**
	 * @see	\Iterator::current()
	 */
	public function current() {
		return $this->childCategories[$this->index];
	}
	
	/**
	 * Returns true if the given category fulfills all needed conditions to
	 * be included in the list.
	 * 
	 * @param	wcf\data\category\Category	$category
	 * @return	boolean
	 */
	public function fulfillsConditions(Category $category) {
		return !in_array($category->categoryID, $this->excludedCategoryIDs) && ($this->includeDisabledCategories || !$category->isDisabled);
	}
	
	/**
	 * @see	\RecursiveIterator::getChildren()
	 */
	public function getChildren() {
		return $this->childCategories[$this->index];
	}
	
	/**
	 * @see	\RecursiveIterator::getChildren()
	 */
	public function hasChildren() {
		return !empty($this->childCategories);
	}
	
	/**
	 * @see	\Iterator::key()
	 */
	public function key() {
		return $this->index;
	}
	
	/**
	 * @see	\Iterator::next()
	 */
	public function next() {
		$this->index++;
	}
	
	/**
	 * @see	\Iterator::rewind()
	 */
	public function rewind() {
		$this->index = 0;
	}
	
	/**
	 * @see	\Iterator::valid()
	 */
	public function valid() {
		return isset($this->childCategories[$this->index]);
	}
}
