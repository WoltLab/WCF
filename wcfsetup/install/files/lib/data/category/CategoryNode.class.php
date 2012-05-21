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
	 * @see	wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\category\Category';
	
	/**
	 * @see	wcf\data\DatabaseObjectDecorator::__construct()
	 */
	public function __construct(DatabaseObject $object, $inludeDisabledCategories = false, array $excludedObjectTypeCategoryIDs = array()) {
		parent::__construct($object);
		
		foreach (CategoryHandler::getInstance()->getChildCategories($this->getDecoratedObject()) as $category) {
			if (!in_array($category->objectTypeCategoryID, $excludedObjectTypeCategoryIDs) && ($inludeDisabledCategories || !$category->isDisabled)) {
				$this->childCategories[] = new CategoryNode($category, $inludeDisabledCategories, $excludedObjectTypeCategoryIDs);
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
