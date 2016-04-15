<?php
namespace wcf\data\menu\item;
use wcf\data\menu\Menu;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\ISortableAction;
use wcf\data\IToggleAction;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Executes menu item related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.menu.item
 * @category	Community Framework
 * @since	2.2
 */
class MenuItemAction extends AbstractDatabaseObjectAction implements ISortableAction, IToggleAction {
	/**
	 * @inheritDoc
	 */
	protected $className = MenuItemEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsCreate = ['admin.content.cms.canManageMenu'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.content.cms.canManageMenu'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['admin.content.cms.canManageMenu'];
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['create', 'delete', 'toggle', 'update'];
	
	/**
	 * @inheritDoc
	 */
	public function validateToggle() {
		parent::validateUpdate();
	
		foreach ($this->objects as $object) {
			if (!$object->canDisable()) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function toggle() {
		foreach ($this->objects as $object) {
			$object->update(array('isDisabled' => ($object->isDisabled) ? 0 : 1));
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateUpdatePosition() {
		WCF::getSession()->checkPermissions(['admin.content.cms.canManageMenu']);
		
		// validate menu id
		$this->readInteger('menuID');
		$menu = new Menu($this->parameters['menuID']);
		if (!$menu->menuID) {
			throw new UserInputException('menuID');
		}
		
		// validate structure
		if (!isset($this->parameters['data']) || !isset($this->parameters['data']['structure']) || !is_array($this->parameters['data']['structure'])) {
			throw new UserInputException('structure');
		}
		
		$menuItemIDs = [];
		foreach ($this->parameters['data']['structure'] as $menuItems) {
			$menuItemIDs = array_merge($menuItemIDs, $menuItems);
		}
		
		$menuItemList = new MenuItemList();
		$menuItemList->getConditionBuilder()->add('menu_item.itemID IN (?)', [$menuItemIDs]);
		$menuItemList->getConditionBuilder()->add('menu_item.menuID = ?', [$this->parameters['menuID']]);
		$menuItemList->readObjects();
		$menuItems = $menuItemList->getObjects();
		
		if (count($menuItems) != count($menuItemIDs)) {
			throw new UserInputException('structure');
		}
		
		foreach ($this->parameters['data']['structure'] as $parentItemID => $children) {
			if ($parentItemID && !isset($menuItems[$parentItemID])) {
				throw new UserInputException('structure');
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function updatePosition() {
		$sql = "UPDATE	wcf".WCF_N."_menu_item
			SET	parentItemID = ?,
				showOrder = ?
			WHERE	itemID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		WCF::getDB()->beginTransaction();
		foreach ($this->parameters['data']['structure'] as $parentItemID => $children) {
			foreach ($children as $showOrder => $menuItemID) {
				$statement->execute([
					($parentItemID ?: null),
					$showOrder + 1,
					$menuItemID
				]);
			}
		}
		WCF::getDB()->commitTransaction();
	}
}
