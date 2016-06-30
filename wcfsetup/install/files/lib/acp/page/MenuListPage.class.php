<?php
namespace wcf\acp\page;
use wcf\data\menu\MenuList;
use wcf\page\SortablePage;

/**
 * Shows a list of menus.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * @since	3.0
 * 
 * @property	MenuList	$objectList
 */
class MenuListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.cms.menu.list';
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = MenuList::class;
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.content.cms.canManageMenu'];
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'title';
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['menuID', 'title', 'position', 'items'];
	
	/**
	 * @inheritDoc
	 */
	public $itemsPerPage = 50;
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlSelects .= '(SELECT COUNT(*) FROM wcf'.WCF_N.'_menu_item WHERE menuID = menu.menuID) AS items, (SELECT position FROM wcf'.WCF_N.'_box WHERE menuID = menu.menuID) AS position';
	}
}
