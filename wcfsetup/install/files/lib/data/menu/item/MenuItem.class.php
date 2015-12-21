<?php
namespace wcf\data\menu\item;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents a menu item.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.menu.item
 * @category	Community Framework
 * @since	2.2
 */
class MenuItem extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'menu_item';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'itemID';
	
	/**
	 * Returns true if the active user can delete this menu item.
	 *
	 * @return boolean
	 */
	public function canDelete() {
		if (WCF::getSession()->getPermission('admin.content.cms.canManageMenu') && !$this->originIsSystem) {
			return true;
		}
			
		return false;
	}
	
	/**
	 * Returns true if the active user can disable this menu item.
	 *
	 * @return boolean
	 */
	public function canDisable() {
		if (WCF::getSession()->getPermission('admin.content.cms.canManageMenu')) {
			return true;
		}
			
		return false;
	}
}
