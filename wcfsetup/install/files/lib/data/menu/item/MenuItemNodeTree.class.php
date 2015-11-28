<?php
namespace wcf\data\menu\item;

/**
 * Represents a menu item node tree.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.menu.item
 * @category	Community Framework
 */
class MenuItemNodeTree {
	/**
	 * menu id
	 * @var	integer
	 */
	public $menuID = null;
	
	/**
	 * list of menu items
	 * @var	array<\wcf\data\menu\item\MenuItem>
	 */
	public $menuItems = array();
	
	/**
	 * menu item structure
	 * @var	array<array>
	 */
	public $menuItemStructure = array();
	
	/**
	 * root node
	 * @var	\wcf\data\menu\item\MenuItemNode
	 */
	public $node = null;
	
	/**
	 * Creates a new MenuItemNodeTree object.
	 *
	 * @param	integer			$menuID
	 */
	public function __construct($menuID) {
		$this->menuID = $menuID;
		
		// load menu items
		$menuItemList = new MenuItemList();
		$menuItemList->getConditionBuilder()->add('menu_item.menuID = ?', array($this->menuID));
		$menuItemList->sqlOrderBy = "menu_item.showOrder";
		$menuItemList->readObjects();
		
		foreach ($menuItemList as $menuItem) {
			$this->menuItems[$menuItem->itemID] = $menuItem;
				
			if (!isset($this->menuItemStructure[$menuItem->parentItemID])) {
				$this->menuItemStructure[$menuItem->parentItemID] = array();
			}
			$this->menuItemStructure[$menuItem->parentItemID][] = $menuItem->itemID;
		}
		
		// generate node tree
		$this->node = new MenuItemNode();
		$this->node->setChildren($this->generateNodeTree(null, $this->node));
	}
	
	/**
	 * Generates the node tree recursively
	 * 
	 * @param	integer					$parentID
	 * @param	\wcf\data\menu\item\MenuItemNode	$parentNode
	 * @param	array<integer>				$filter
	 * @return	array<\wcf\data\menu\item\MenuItemNode>
	 */
	protected function generateNodeTree($parentID = null, MenuItemNode $parentNode = null) {
		$nodes = array();
		
		$itemIDs = (isset($this->menuItemStructure[$parentID]) ? $this->menuItemStructure[$parentID] : array());
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
	 * @return	array<\wcf\data\menu\item\MenuItemNode>
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
