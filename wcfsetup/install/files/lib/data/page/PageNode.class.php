<?php
namespace wcf\data\page;

/**
 * Represents a page node element.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.page
 * @category	Community Framework
 */
class PageNode implements \Countable, \RecursiveIterator {
	/**
	 * parent node
	 * @var	\wcf\data\page\PageNode
	 */
	protected $parentNode = null;
	
	/**
	 * children of this node
	 * @var	array<\wcf\data\page\PageNode>
	 */
	protected $children = array();
	
	/**
	 * page object
	 * @var	\wcf\data\page\Page
	 */
	protected $page = null;
	
	/**
	 * node depth
	 * @var	integer
	 */
	protected $depth = 0;
	
	/**
	 * iterator position
	 * @var	integer
	 */
	private $position = 0;
	
	/**
	 * Creates a new PageNode object.
	 * 
	 * @param	\wcf\data\page\PageNode		$parentNode
	 * @param	\wcf\data\page\Page		$page
	 * @param	integer				$depth
	 */
	public function __construct($parentNode = null, Page $page = null, $depth = 0) {
		$this->parentNode = $parentNode;
		$this->page = $page;
		$this->depth = $depth;
	}
	
	/**
	 * Sets the children of this node.
	 * 
	 * @param	array<\wcf\data\page\PageNode>		$children
	 */
	public function setChildren(array $children) {
		$this->children = $children;
	}
	
	/**
	 * Returns the parent node
	 * 
	 * @return	\wcf\data\page\PageNode
	 */
	public function getParentNode() {
		return $this->parentNode;
	}
	
	/**
	 * Returns the page object of this node
	 * 
	 * @return	\wcf\data\page\Page
	 */
	public function getPage() {
		return $this->page;
	}
	
	/**
	 * Returns the number of children.
	 * 
	 * @return	integer
	 */
	public function count() {
		return count($this->children);
	}
	
	/**
	 * @see	\Iterator::rewind()
	 */
	public function rewind() {
		$this->position = 0;
	}
	
	/**
	 * @see	\Iterator::valid()
	 */
	public function valid() {
		return isset($this->children[$this->position]);
	}
	
	/**
	 * @see	\Iterator::next()
	 */
	public function next() {
		$this->position++;
	}
	
	/**
	 * @see	\Iterator::current()
	 */
	public function current() {
		return $this->children[$this->position];
	}
	
	/**
	 * @see	\Iterator::key()
	 */
	public function key() {
		return $this->position;
	}
	
	/**
	 * @see	\RecursiveIterator::getChildren()
	 */
	public function getChildren() {
		return $this->children[$this->position];
	}
	
	/**
	 * @see	\RecursiveIterator::hasChildren()
	 */
	public function hasChildren() {
		return count($this->children) > 0;
	}
	
	/**
	 * Returns node depth.
	 * 
	 * @return	integer
	 */
	public function getDepth() {
		return $this->depth;
	}
}
