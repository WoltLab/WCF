<?php
namespace wcf\data\page\menu\item;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\ISortableAction;
use wcf\data\IToggleAction;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Executes page menu item-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.page.menu.item
 * @category	Community Framework
 */
class PageMenuItemAction extends AbstractDatabaseObjectAction implements ISortableAction, IToggleAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\page\menu\item\PageMenuItemEditor';
	
	/**
	 * page menu item editor
	 * @var	\wcf\data\page\menu\item\PageMenuItemEditor
	 */
	public $menuItemEditor = null;
	
	/**
	 * list of menu items
	 * @var	array<\wcf\data\page\menu\item\PageMenuItem>
	 */
	public $menuItems = array();
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.display.canManagePageMenu');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsUpdate
	 */
	protected $permissionsUpdate = array('admin.display.canManagePageMenu');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$requireACP
	 */
	protected $requireACP = array('delete', 'toggle', 'update', 'updatePosition');
	
	/**
	 * @see	\wcf\data\IDatabaseObjectAction::create()
	 */
	public function create() {
		if (!isset($this->parameters['data']['packageID'])) {
			$this->parameters['data']['packageID'] = PACKAGE_ID;
		}
		
		// calculate show order
		$this->parameters['data']['showOrder'] = PageMenuItemEditor::getShowOrder($this->parameters['data']['showOrder'], $this->parameters['data']['menuPosition'], $this->parameters['data']['parentMenuItem']);
		
		$menuItem = parent::create();
		
		if ($menuItem->menuPosition == 'header') {
			PageMenuItemEditor::updateLandingPage();
		}
		
		return $menuItem;
	}
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::delete()
	 */
	public function delete() {
		$returnValues = parent::delete();
		
		PageMenuItemEditor::updateLandingPage();
		
		return $returnValues;
	}
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::update()
	 */
	public function update() {
		parent::update();
		
		PageMenuItemEditor::updateLandingPage();
	}
	
	/**
	 * @see	\wcf\data\ISortableAction::validateUpdatePosition()
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
	 * @see	\wcf\data\ISortableAction::updatePosition()
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
					$showOrder + 1,
					$menuItemID
				));
			}
		}
		WCF::getDB()->commitTransaction();
		
		// update landing page
		if ($this->parameters['menuPosition'] == 'header') {
			PageMenuItemEditor::updateLandingPage();
		}
	}
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::validateDelete()
	 */
	public function validateDelete() {
		parent::validateDelete();
		
		foreach ($this->objects as $pageMenuItem) {
			if (!$pageMenuItem->canDelete()) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * @see	\wcf\data\IToggleAction::validateToggle()
	 */
	public function validateToggle() {
		$this->menuItemEditor = $this->getSingleObject();
		if ($this->menuItemEditor->isLandingPage) {
			throw new PermissionDeniedException();
		}
		
		WCF::getSession()->checkPermissions($this->permissionsUpdate);
	}
	
	/**
	 * @see	\wcf\data\IToggleAction::toggle()
	 */
	public function toggle() {
		$this->menuItemEditor->update(array(
			'isDisabled' => ($this->menuItemEditor->isDisabled ? 0 : 1)
		));
	}
}
