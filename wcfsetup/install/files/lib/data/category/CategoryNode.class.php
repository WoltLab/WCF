<?php
namespace wcf\data\category;
use wcf\data\DatabaseObjectDecorator;

/**
 * Represents a category node.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.category
 * @category	Community Framework
 */
class CategoryNode extends DatabaseObjectDecorator implements \RecursiveIterator, \Countable {
	/**
	 * child category nodes
	 * @var	array<wcf\data\category\CategoryNode>
	 */
	protected $children = array();
	
	/**
	 * current iterator key
	 * @var	integer
	 */
	protected $index = 0;
	
	/**
	 * @see	wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\category\Category';
	
	/**
	 * Adds the given category node as child node.
	 * 
	 * @param	wcf\data\category\CategoryNode		$categoryNode
	 */
	public function addChild(CategoryNode $categoryNode) {
		$this->children[] = $categoryNode;
	}
	
	/**
	 * @see	\Countable::count()
	 */
	public function count() {
		return count($this->children);
	}
	
	/**
	 * @see	\Iterator::current()
	 */
	public function current() {
		return $this->children[$this->index];
	}
	
	/**
	 * @see	\RecursiveIterator::getChildren()
	 */
	public function getChildren() {
		return $this->children[$this->index];
	}
	
	/**
	 * @see	\RecursiveIterator::getChildren()
	 */
	public function hasChildren() {
		return !empty($this->children);
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
		return isset($this->children[$this->index]);
	}
}
