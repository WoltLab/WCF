<?php
namespace wcf\data\category;
use wcf\data\DatabaseObjectDecorator;

/**
 * Represents a category node.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Category
 * 
 * @method	Category	getDecoratedObject()
 * @mixin	Category
 */
class CategoryNode extends DatabaseObjectDecorator implements \RecursiveIterator, \Countable {
	/**
	 * child category nodes
	 * @var	CategoryNode[]
	 */
	protected $children = [];
	
	/**
	 * current iterator key
	 * @var	integer
	 */
	protected $index = 0;
	
	/**
	 * parent node object
	 * @var	CategoryNode
	 */
	protected $parentNode = null;
	
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Category::class;
	
	/**
	 * Adds the given category node as child node.
	 * 
	 * @param	CategoryNode		$categoryNode
	 */
	public function addChild(CategoryNode $categoryNode) {
		$categoryNode->setParentNode($this);
		
		$this->children[] = $categoryNode;
	}
	
	/**
	 * Sets parent node object.
	 * 
	 * @param	CategoryNode		$parentNode
	 */
	public function setParentNode(CategoryNode $parentNode) {
		$this->parentNode = $parentNode;
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
	 * Returns node depth.
	 *
	 * @return	integer
	 */
	public function getDepth() {
		$element = $this;
		$depth = 1;
		
		while ($element->parentNode->parentNode != null) {
			$depth++;
			$element = $element->parentNode;
		}
		
		return $depth;
	}
	
	/**
	 * @inheritDoc
	 */
	public function count() {
		return count($this->children);
	}
	
	/**
	 * @inheritDoc
	 */
	public function current() {
		return $this->children[$this->index];
	}
	
	/**
	 * @inheritDoc
	 */
	public function getChildren() {
		return $this->children[$this->index];
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasChildren() {
		return !empty($this->children);
	}
	
	/**
	 * @inheritDoc
	 */
	public function key() {
		return $this->index;
	}
	
	/**
	 * @inheritDoc
	 */
	public function next() {
		$this->index++;
	}
	
	/**
	 * @inheritDoc
	 */
	public function rewind() {
		$this->index = 0;
	}
	
	/**
	 * @inheritDoc
	 */
	public function valid() {
		return isset($this->children[$this->index]);
	}
}
