<?php
namespace wcf\data\menu;
use wcf\data\box\Box;
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
	 * menu item node list
	 * @var \RecursiveIteratorIterator
	 */
	protected $menuItemNodeList = null;
	
	/**
	 * box object
	 * @var Box
	 */
	protected $box = null;
	
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
	 * @return      \RecursiveIteratorIterator
	 */
	public function getMenuItemNodeList() {
		if ($this->menuItemNodeList === null) {
			$this->menuItemNodeList = MenuCache::getInstance()->getMenuItemsByMenuID($this->menuID)->getNodeList();
		}
		
		return $this->menuItemNodeList;
	}
	
	/**
	 * Returns false if this menu has no content (has menu items).
	 *
	 * @return	boolean
	 */
	public function hasContent() {
		return true; // @todo
		//return count(MenuCache::getInstance()->getMenuItemsByMenuID($this->menuID)->getNodeList());
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
	 * @return      Box
	 */
	public function getBox() {
		if ($this->box === null) {
			$this->box = Box::getBoxByMenuID($this->menuID);
		}
		
		return $this->box;
	}
}
