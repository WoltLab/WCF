<?php
namespace wcf\data\menu;
use wcf\data\box\Box;
use wcf\data\menu\item\MenuItemNodeTree;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents a menu.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.menu
 * @category	Community Framework
 * @since	2.2
 */
class Menu extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'menu';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'menuID';
	
	/**
	 * menu item node tree
	 * @var	MenuItemNodeTree
	 */
	protected $menuItemNodeTree;
	
	/**
	 * box object
	 * @var	Box
	 */
	protected $box;
	
	/**
	 * Returns true if the active user can delete this menu.
	 * 
	 * @return	boolean
	 */
	public function canDelete() {
		if (WCF::getSession()->getPermission('admin.content.cms.canManageMenu') && !$this->originIsSystem) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns the items of this menu.
	 * 
	 * @return	\RecursiveIteratorIterator
	 */
	public function getMenuItemNodeList() {
		return $this->getMenuItemNodeTree()->getNodeList();
	}
	
	/**
	 * Returns false if this menu has no content (has menu items).
	 *
	 * @return	boolean
	 */
	public function hasContent() {
		return $this->getMenuItemNodeTree()->getVisibleItemCount() > 0;
	}
	
	/**
	 * Returns the title for the rendered version of this menu.
	 *
	 * @return	string
	 */
	public function getTitle() {
		return WCF::getLanguage()->get($this->title);
	}
	
	/**
	 * Returns the content for the rendered version of this menu.
	 *
	 * @return	string
	 */
	public function getContent() {
		WCF::getTPL()->assign(['menuItemNodeList' => $this->getMenuItemNodeList()]);
		return WCF::getTPL()->fetch('__menu');
	}
	
	/**
	 * Returns the box of this menu.
	 * 
	 * @return	Box
	 */
	public function getBox() {
		if ($this->box === null) {
			$this->box = Box::getBoxByMenuID($this->menuID);
		}
		
		return $this->box;
	}
	
	/**
	 * Returns the menu item node tree with the menu's items.
	 * 
	 * @return	MenuItemNodeTree
	 */
	protected function getMenuItemNodeTree() {
		if ($this->menuItemNodeTree === null) {
			$this->menuItemNodeTree = new MenuItemNodeTree($this->menuID, MenuCache::getInstance()->getMenuItemsByMenuID($this->menuID));
		}
		
		return $this->menuItemNodeTree;
	}
}
