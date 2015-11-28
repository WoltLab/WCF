<?php
namespace wcf\data\menu\item;

/**
 * Represents a menu item node element.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.menu.item
 * @category	Community Framework
 */
class MenuItemNode implements \Countable, \RecursiveIterator {
	/**
	 * parent node
	 * @var	\wcf\data\menu\item\MenuItemNode
	 */
	protected $parentNode = null;
	
	/**
	 * children of this node
	 * @var	array<\wcf\data\menu\item\MenuItemNode>
	 */
	protected $children = array();
	
	/**
	 * menu item object
	 * @var	\wcf\data\menu\item\MenuItem
	 */
	protected $menuItem = null;
	
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
	 * Creates a new MenuItemNode object.
	 * 
	 * @param	\wcf\data\menu\item\MenuItemNode	$parentNode
	 * @param	\wcf\data\menu\item\MenuItem		$menuItem
	 * @param	integer					$depth
	 */
	public function __construct($parentNode = null, MenuItem $menuItem = null, $depth = 0) {
		$this->parentNode = $parentNode;
		$this->menuItem = $menuItem;
		$this->depth = $depth;
	}
	
	/**
	 * Sets the children of this node.
	 * 
	 * @param	array<\wcf\data\menu\item\MenuItemNode>		$children
	 */
	public function setChildren(array $children) {
		$this->children = $children;
	}
	
	/**
	 * Returns the parent node
	 * 
	 * @return	\wcf\data\menu\item\MenuItemNode
	 */
	public function getParentNode() {
		return $this->parentNode;
	}
	
	/**
	 * Returns the menu item object of this node.
	 * 
	 * @return	\wcf\data\menu\item\MenuItem
	 */
	public function getMenuItem() {
		return $this->menuItem;
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
	 * Returns true if this element is the last sibling.
	 *
	 * @return	boolean
	 */
	public function isLastSibling() {
		foreach ($this->parentNode as $key => $child) {
			if ($child === $this) {
				if ($key == count($this->parentNode) - 1) return true;
				return false;
			}
		}
	}
	
	/**
	 * Returns the number of open parent nodes.
	 *
	 * @return	integer
	 */
	public function getOpenParentNodes() {
		$element = $this;
		$i = 0;
	
		while ($element->parentNode->parentNode != null && $element->isLastSibling()) {
			$i++;
			$element = $element->parentNode;
		}
	
		return $i;
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
