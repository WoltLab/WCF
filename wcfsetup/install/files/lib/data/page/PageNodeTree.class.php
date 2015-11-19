<?php
namespace wcf\data\page;

/**
 * Represents a page node tree.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.page
 * @category	Community Framework
 */
class PageNodeTree {
	/**
	 * parent page id
	 * @var	integer
	 */
	public $parentID = null;
	
	/**
	 * start depth
	 * @var	integer
	 */
	public $startDepth = 0;
	
	/**
	 * list of pages
	 * @var	array<\wcf\data\page\Page>
	 */
	public $pages = array();
	
	/**
	 * page structure
	 * @var	array<array>
	 */
	public $pageStructure = array();
	
	/**
	 * root node
	 * @var	\wcf\data\page\PageNode
	 */
	public $node = null;
	
	/**
	 * Creates a new PageNodeTree object.
	 *
	 * @param	integer			$parentID
	 * @param	integer			$startDepth
	 */
	public function __construct($parentID = null, $startDepth = 0) {
		$this->parentID = $parentID;
		$this->startDepth = $startDepth;
		
		// load pages
		$pageList = new PageList();
		$pageList->sqlOrderBy = "page.displayName";
		$pageList->readObjects();
		
		foreach ($pageList as $page) {
			$this->pages[$page->pageID] = $page;
				
			if (!isset($this->pageStructure[$page->parentPageID])) {
				$this->pageStructure[$page->parentPageID] = array();
			}
			$this->pageStructure[$page->parentPageID][] = $page->pageID;
		}
		
		// generate node tree
		$this->node = new PageNode(null, null, $startDepth);
		$this->node->setChildren($this->generateNodeTree($parentID, $this->node));
	}
	
	/**
	 * Generates the node tree recursively
	 * 
	 * @param	integer				$parentID
	 * @param	\wcf\data\page\PageNode		$parentNode
	 * @param	array<integer>			$filter
	 * @return	array<\wcf\data\page\PageNode>
	 */
	protected function generateNodeTree($parentID, PageNode $parentNode = null) {
		$nodes = array();
		
		$pageIDs = (isset($this->pageStructure[$parentID]) ? $this->pageStructure[$parentID] : array());
		foreach ($pageIDs as $pageID) {
			$page = $this->pages[$pageID];
			$node = new PageNode($parentNode, $page, ($parentNode !== null ? ($parentNode->getDepth() + 1) : 0));
			$nodes[] = $node;
				
			// get children
			$node->setChildren($this->generateNodeTree($pageID, $node));
		}
		
		return $nodes;
	}
	
	/**
	 * Returns the page node tree.
	 * 
	 * @return	array<\wcf\data\page\PageNode>
	 */
	public function getNodeTree() {
		return $this->node->getChildren();
	}
	
	/**
	 * Returns the iteratable node list
	 *
	 * @return	\RecursiveIteratorIterator
	 */
	public function getNodeList() {
		return new \RecursiveIteratorIterator($this->node, \RecursiveIteratorIterator::SELF_FIRST);
	}
}
