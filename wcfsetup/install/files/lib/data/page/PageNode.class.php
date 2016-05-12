<?php
namespace wcf\data\page;
use wcf\data\DatabaseObjectDecorator;

/**
 * Represents a page node element.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.page
 * @category	Community Framework
 * @since	2.2
 * 
 * @method	Page	getDecoratedObject()
 * @mixin	Page
 */
class PageNode extends DatabaseObjectDecorator implements \Countable, \RecursiveIterator {
	/**
	 * parent node
	 * @var	PageNode
	 */
	protected $parentNode = null;
	
	/**
	 * children of this node
	 * @var	PageNode[]
	 */
	protected $children = [];
	
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
	 * @inheritDoc
	 */
	protected static $baseClass = Page::class;
	
	/**
	 * Creates a new PageNode object.
	 * 
	 * @param	PageNode	$parentNode
	 * @param	Page		$page
	 * @param	integer		$depth
	 */
	public function __construct($parentNode = null, Page $page = null, $depth = 0) {
		if ($page === null) {
			$page = new Page(null, []);
		}
		parent::__construct($page);
		
		$this->parentNode = $parentNode;
		$this->depth = $depth;
	}
	
	/**
	 * Sets the children of this node.
	 * 
	 * @param	PageNode[]	$children
	 */
	public function setChildren(array $children) {
		$this->children = $children;
	}
	
	/**
	 * Returns the parent node
	 * 
	 * @return	PageNode
	 */
	public function getParentNode() {
		return $this->parentNode;
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
	 * @inheritDoc
	 */
	public function rewind() {
		$this->position = 0;
	}
	
	/**
	 * @inheritDoc
	 */
	public function valid() {
		return isset($this->children[$this->position]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function next() {
		$this->position++;
	}
	
	/**
	 * @inheritDoc
	 */
	public function current() {
		return $this->children[$this->position];
	}
	
	/**
	 * @inheritDoc
	 */
	public function key() {
		return $this->position;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getChildren() {
		return $this->children[$this->position];
	}
	
	/**
	 * @inheritDoc
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
