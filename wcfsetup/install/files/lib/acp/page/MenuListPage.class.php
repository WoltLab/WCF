<?php
namespace wcf\acp\page;
use wcf\page\SortablePage;

/**
 * Shows a list of menus.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 * @since	2.2
 */
class MenuListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.cms.menu.list';
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = 'wcf\data\menu\MenuList';
	
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
	public $validSortFields = ['menuID', 'title', 'items'];
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlSelects .= '(SELECT COUNT(*) FROM wcf'.WCF_N.'_menu_item WHERE menuID = menu.menuID) AS items';
	}
}
