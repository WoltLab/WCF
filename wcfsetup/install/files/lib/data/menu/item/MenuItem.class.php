<?php
namespace wcf\data\menu\item;
use wcf\data\page\Page;
use wcf\data\page\PageCache;
use wcf\data\DatabaseObject;
use wcf\system\exception\SystemException;
use wcf\system\page\handler\ILookupPageHandler;
use wcf\system\page\handler\IMenuPageHandler;
use wcf\system\WCF;

/**
 * Represents a menu item.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
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
	 * @var IMenuPageHandler
	 */
	protected $handler;
	
	/**
	 * page object
	 * @var Page
	 */
	protected $page;
	
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
		if ($this->pageObjectID) {
			$handler = $this->getMenuPageHandler();
			if ($handler && $handler instanceof ILookupPageHandler) {
				return $handler->getLink($this->pageObjectID);
			}
		}
		
		if ($this->pageID) {
			return $this->getPage()->getLink();
		}
		else {
			return $this->externalURL;
		}
	}
	
	/**
	 * Returns the page that is linked by this menu item.
	 * 
	 * @return      Page|null
	 */
	public function getPage() {
		if ($this->page === null && $this->pageID) {
			$this->page = PageCache::getInstance()->getPage($this->pageID);
		}
		
		return $this->page;
	}
	
	/**
	 * Returns false if this item should be hidden from menu.
	 * 
	 * @return      boolean
	 */
	public function isVisible() {
		if ($this->getPage() !== null && !$this->getPage()->isVisible()) {
			return false;
		}
		
		if ($this->getMenuPageHandler() !== null) {
			return $this->getMenuPageHandler()->isVisible($this->pageObjectID ?: null);
		}
		
		return true;
	}
	
	/**
	 * Returns the number of outstanding items for this menu.
	 * 
	 * @return      integer
	 */
	public function getOutstandingItems() {
		if ($this->getMenuPageHandler() !== null) {
			return $this->getMenuPageHandler()->getOutstandingItemCount($this->pageObjectID ?: null);
		}
		
		return 0;
	}
	
	/**
	 * @return      IMenuPageHandler|null
	 */
	protected function getMenuPageHandler() {
		$page = $this->getPage();
		if ($page !== null && $page->handler) {
			if ($this->handler === null) {
				$className = $this->getPage()->handler;
				$this->handler = new $className;
				if (!($this->handler instanceof IMenuPageHandler)) {
					throw new SystemException("Expected a valid handler implementing '" . IMenuPageHandler::class . "'.");
				}
			}
		}
		
		return $this->handler;
	}
}
