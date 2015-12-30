<?php
namespace wcf\data\menu\item;
use wcf\data\DatabaseObject;
use wcf\data\page\Page;
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
	 * page object
	 * @var Page
	 */
	protected $page = null;
	
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
	
	/**
	 * Returns the URL of this menu item.
	 * 
	 * @return      string
	 */
	public function getURL() {
		if ($this->pageID) {
			return $this->getPage()->getURL();
		}
		else {
			return $this->externalURL;
		}
	}
	
	/**
	 * Returns the page that is linked by this menu item.
	 * 
	 * @return      Page
	 */
	public function getPage() {
		if ($this->page === null) {
			if ($this->pageID) {
				$this->page = new Page($this->pageID);
			}
		}
		
		return $this->page;
	}
}
