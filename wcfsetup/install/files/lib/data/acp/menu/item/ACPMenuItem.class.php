<?php
namespace wcf\data\acp\menu\item;
use wcf\data\DatabaseObject;
use wcf\system\menu\TreeMenuItem;
use wcf\system\request\LinkHandler;

/**
 * Represents an ACP menu item.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.menu.item
 * @category 	Community Framework
 */
class ACPMenuItem extends DatabaseObject implements TreeMenuItem {
	/**
	 * @see	DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'acp_menu_item';
	
	/**
	 * @see	DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'menuItemID';
	
	/**
	 * @see TreeMenuItem::getLink()
	 */
	public function getLink() {
		return LinkHandler::getInstance()->getLink($this->menuItemLink);
	}
}
