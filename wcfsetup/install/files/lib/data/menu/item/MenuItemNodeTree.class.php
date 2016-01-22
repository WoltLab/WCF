<?php
namespace wcf\data\menu\item;

/**
 * Represents a menu item node tree.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.menu.item
 * @category	Community Framework
 * @since	2.2
 */
class MenuItemNodeTree {
	/**
	 * menu id
	 * @var	integer
	 */
	public $menuID;
	
	/**
	 * list of menu items
	 * @var	MenuItem[]
	 */
	public $menuItems = [];
	
	/**
	 * menu item structure
	 * @var	mixed[]
	 */
	public $menuItemStructure = [];
	
	/**
	 * root node
	 * @var	MenuItemNode
	 */
	public $node;
	
	/**
	 * Creates a new MenuItemNodeTree object.
	 * 
	 * @param	integer		$menuID         menu id
	 * @param       MenuItemList    $menuItemList   optional object to be provided when building the tree from cache
	 */
	public function __construct($menuID, MenuItemList $menuItemList = null) {
		$this->menuID = $menuID;
		
		// load menu items
		if ($menuItemList === null) {
			$menuItemList = new MenuItemList();
			$menuItemList->getConditionBuilder()->add('menu_item.menuID = ?', [$this->menuID]);
			$menuItemList->sqlOrderBy = "menu_item.showOrder";
			$menuItemList->readObjects();
		}
		
		foreach ($menuItemList as $menuItem) {
			$this->menuItems[$menuItem->itemID] = $menuItem;
				
			if (!isset($this->menuItemStructure[$menuItem->parentItemID])) {
				$this->menuItemStructure[$menuItem->parentItemID] = [];
			}
			$this->menuItemStructure[$menuItem->parentItemID][] = $menuItem->itemID;
		}
		
		// filter items by visibility
		foreach ($this->menuItems as $menuItemID => $menuItem) {
			if (!$menuItem->isVisible()) {
				unset($this->menuItems[$menuItemID]);
				unset($this->menuItemStructure[$menuItemID]);
				
				// remove item from parent item structure
				$key = array_search($menuItemID, $this->menuItemStructure[$menuItem->parentItemID]);
				array_splice($this->menuItemStructure[$menuItem->parentItemID], $key, 1);
			}
		}
		
		// generate node tree
		$this->node = new MenuItemNode();
		$this->node->setChildren($this->generateNodeTree(null, $this->node));
	}
	
	/**
	 * Generates the node tree recursively
	 * 
	 * @param	integer			$parentID       parent menu item id
	 * @param	MenuItemNode		$parentNode     parent menu item object
	 * @return	MenuItemNode[]          nested menu item tree
	 */
	protected function generateNodeTree($parentID = null, MenuItemNode $parentNode = null) {
		$nodes = array();
		
		$itemIDs = (isset($this->menuItemStructure[$parentID]) ? $this->menuItemStructure[$parentID] : []);
		foreach ($itemIDs as $itemID) {
			$menuItem = $this->menuItems[$itemID];
			$node = new MenuItemNode($parentNode, $menuItem, ($parentNode !== null ? ($parentNode->getDepth() + 1) : 0));
			$nodes[] = $node;
				
			// get children
			$node->setChildren($this->generateNodeTree($itemID, $node));
		}
		
		return $nodes;
	}
	
	/**
	 * Returns the menu item node tree.
	 * 
	 * @return	MenuItemNode[]
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
