<?php
namespace wcf\data\menu;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents a menu.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.menu
 * @category	Community Framework
 */
class Menu extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'menu';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'menuID';
	
	/**
	 * Returns true if the active user can delete this menu.
	 * 
	 * @return boolean
	 */
	public function canDelete() {
		if (WCF::getSession()->getPermission('admin.content.cms.canManageMenu') && !$this->originIsSystem) {
			return true;
		}
			
		return false;
	}
}
