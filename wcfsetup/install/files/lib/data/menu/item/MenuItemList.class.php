<?php
namespace wcf\data\menu\item;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of menu items.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.menu.item
 * @category	Community Framework
 */
class MenuItemList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = MenuItem::class;
}
