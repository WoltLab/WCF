<?php
namespace wcf\data\menu;
use wcf\data\menu\item\MenuItemNodeTree;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\WCF;

class ViewableMenu extends DatabaseObjectDecorator {
	/**
	 * @var MenuItemNodeTree
	 */
	protected $menuItems;
	
	public function hasContent() {
		// TODO
		return true;
	}
	
	public function getContent() {
		return WCF::getTPL()->fetch('__menu', 'wcf', ['menuItems' => $this->menuItems->getNodeList()]);
	}
	
	protected function getMenuItems() {
		if ($this->menuItems === null) {
			$this->menuItems = MenuCache::getInstance()->getMenuItemsByMenuID($this->object->menuID);
		}
		
		return $this->menuItems;
	}
}
