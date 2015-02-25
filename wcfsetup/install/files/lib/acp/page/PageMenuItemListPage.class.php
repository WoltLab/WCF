<?php
namespace wcf\acp\page;
use wcf\data\page\menu\item\PageMenuItemList;
use wcf\data\page\menu\item\ViewablePageMenuItem;
use wcf\page\AbstractPage;
use wcf\system\WCF;

/**
 * Shows a list of page menu items.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 */
class PageMenuItemListPage extends AbstractPage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.pageMenu.list';
	
	/**
	 * list of footer page menu items
	 * @var	array<\wcf\data\page\menu\item\PageMenuItem>
	 */
	public $footerItems = array();
	
	/**
	 * list of header page menu items
	 * @var	array<\wcf\data\page\menu\item\PageMenuItem>
	 */
	public $headerItems = array();
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.display.canManagePageMenu');
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		$menuItemList = new PageMenuItemList();
		$menuItemList->sqlOrderBy = "page_menu_item.parentMenuItem ASC, page_menu_item.showOrder ASC";
		$menuItemList->readObjects();
		
		foreach ($menuItemList as $menuItem) {
			if ($menuItem->menuPosition == 'footer') {
				if ($menuItem->parentMenuItem) {
					$this->footerItems[$menuItem->parentMenuItem]->addChild($menuItem);
				}
				else {
					$this->footerItems[$menuItem->menuItem] = $menuItem;
				}
			}
			else {
				if ($menuItem->parentMenuItem) {
					if (isset($this->headerItems[$menuItem->parentMenuItem])) {
						$this->headerItems[$menuItem->parentMenuItem]->addChild($menuItem);
					}
				}
				else {
					$this->headerItems[$menuItem->menuItem] = new ViewablePageMenuItem($menuItem);
				}
			}
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'footerItems' => $this->footerItems,
			'headerItems' => $this->headerItems
		));
	}
}
