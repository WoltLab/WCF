<?php
namespace wcf\data\page\menu\item;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\ISortableAction;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Executes page menu item-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.page.menu.item
 * @category	Community Framework
 */
class PageMenuItemAction extends AbstractDatabaseObjectAction implements ISortableAction {
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\page\menu\item\PageMenuItemEditor';
	
	/**
	 * list of menu items
	 * @var	array<wcf\data\page\menu\item\PageMenuItem>
	 */
	public $menuItems = array();
	
	/**
	 * @see wcf\data\IDatabaseObjectAction::create()
	 */
	public function create() {
		if (!isset($this->parameters['data']['packageID'])) {
			$this->parameters['data']['packageID'] = PACKAGE_ID;
		}
		
		$menuItem = parent::create();
		
		if ($menuItem->isLandingPage) {
			$menuItemEditor = new PageMenuItemEditor($menuItem);
			$menuItemEditor->setAsLandingPage();
		}
		
		return $menuItem;
	}
	
	/**
	 * @see	wcf\data\ISortableAction::validateUpdatePosition()
	 */
	public function validateUpdatePosition() {
		WCF::getSession()->checkPermissions(array('admin.display.canManagePageMenu'));
		
		if (!isset($this->parameters['data']) || !isset($this->parameters['data']['structure']) || !is_array($this->parameters['data']['structure'])) {
			throw new UserInputException('structure');
		}
		
		if (!isset($this->parameters['menuPosition']) || !in_array($this->parameters['menuPosition'], array('footer', 'header'))) {
			throw new UserInputException('structure');
		}
		
		if ($this->parameters['menuPosition'] == 'footer') {
			if (count($this->parameters['data']['structure']) > 1 || !isset($this->parameters['data']['structure'][0])) {
				throw new UserInputException('structure');
			}
		}
		
		$menuItemIDs = array();
		foreach ($this->parameters['data']['structure'] as $menuItems) {
			$menuItemIDs = array_merge($menuItemIDs, $menuItems);
		}
		
		$menuItemList = new PageMenuItemList();
		$menuItemList->getConditionBuilder()->add("page_menu_item.menuItemID IN (?)", array($menuItemIDs));
		$menuItemList->getConditionBuilder()->add("page_menu_item.menuPosition = ?", array($this->parameters['menuPosition']));
		$menuItemList->sqlLimit = 0;
		$menuItemList->readObjects();
		$this->menuItems = $menuItemList->getObjects();
		
		if (count($this->menuItems) != count($menuItemIDs)) {
			throw new UserInputException('structure');
		}
		
		foreach ($this->parameters['data']['structure'] as $parentMenuItemID => $menuItems) {
			if ($parentMenuItemID && !isset($this->menuItems[$parentMenuItemID])) {
				throw new UserInputException('structure');
			}
		}
	}
	
	/**
	 * @see	wcf\data\ISortableAction::updatePosition()
	 */
	public function updatePosition() {
		$sql = "UPDATE	wcf".WCF_N."_page_menu_item
			SET	parentMenuItem = ?,
				showOrder = ?
			WHERE	menuItemID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		WCF::getDB()->beginTransaction();
		foreach ($this->parameters['data']['structure'] as $parentMenuItemID => $menuItems) {
			foreach ($menuItems as $showOrder => $menuItemID) {
				$statement->execute(array(
					($parentMenuItemID ? $this->menuItems[$parentMenuItemID]->menuItem : ''),
					$showOrder,
					$menuItemID
				));
			}
		}
		WCF::getDB()->commitTransaction();
	}
}
