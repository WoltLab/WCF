<?php
namespace wcf\data\acp\menu\item;
use wcf\data\DatabaseObject;
use wcf\system\menu\ITreeMenuItem;
use wcf\system\request\LinkHandler;

/**
 * Represents an ACP menu item.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.menu.item
 * @category	Community Framework
 */
class ACPMenuItem extends DatabaseObject implements ITreeMenuItem {
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'acp_menu_item';
	
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'menuItemID';
	
	/**
	 * @see	wcf\system\menu\ITreeMenuItem::getLink()
	 */
	public function getLink() {
		return LinkHandler::getInstance()->getLink(null, array(), $this->menuItemLink);
	}
}
