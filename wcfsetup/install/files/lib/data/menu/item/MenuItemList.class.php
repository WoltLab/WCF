<?php
namespace wcf\data\menu\item;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of menu items.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.menu.item
 * @category	Community Framework
 * @since	2.2
 *
 * @method	MenuItem	current()
 * @method	MenuItem[]	getObjects()
 * @method	MenuItem|null	search($objectID)
 * @property	MenuItem[]	$objects
 */
class MenuItemList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = MenuItem::class;
	
	/**
	 * Sets the menu items used to improve menu cache performance.
	 * 
	 * @param	MenuItem[]	$menuItems	list of menu item objects
	 */
	public function setMenuItems(array $menuItems) {
		$this->objects = $menuItems;
		$this->indexToObject = $this->objectIDs = array_keys($this->objects);
	}
}
