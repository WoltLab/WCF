<?php
namespace wcf\acp\page;
use wcf\data\menu\item\MenuItemNodeTree;
use wcf\data\menu\Menu;
use wcf\page\AbstractPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Shows a list of menu items.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 * @since	2.2
 */
class MenuItemListPage extends AbstractPage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.cms.menu.list';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.content.cms.canManageMenu');
	
	/**
	 * menu item list
	 * @var	\wcf\data\menu\item\MenuItemList
	 */
	public $objectList = null;
	
	/**
	 * menu item node tree
	 * @var	MenuItemNodeTree
	 */
	public $menuItems = null;
	
	/**
	 * menu id
	 * @var	integer
	 */
	public $menuID = 0;
	
	/**
	 * menu object
	 * @var	Menu
	 */
	public $menu = null;
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
	
		if (isset($_REQUEST['id'])) $this->menuID = intval($_REQUEST['id']);
		$this->menu = new Menu($this->menuID);
		if (!$this->menu->menuID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->menuItems = new MenuItemNodeTree($this->menuID);
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'menuID' => $this->menuID,
			'menu' => $this->menu,
			'menuItemNodeList' => $this->menuItems->getNodeList()
		));
	}
}
