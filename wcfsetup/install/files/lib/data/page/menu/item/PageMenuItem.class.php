<?php
namespace wcf\data\page\menu\item;
use wcf\data\DatabaseObject;
use wcf\system\exception\SystemException;
use wcf\system\menu\page\DefaultPageMenuItemProvider;
use wcf\system\menu\TreeMenuItem;
use wcf\system\request\LinkHandler;
use wcf\util\ClassUtil;

/**
 * Represents an page menu item.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.page.menu.item
 * @category 	Community Framework
 */
class PageMenuItem extends DatabaseObject implements TreeMenuItem {
	/**
	 * @see	DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'page_menu_item';
	
	/**
	 * @see	DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'menuItemID';
	
	/**
	 * item provider for this page menu item
	 * @var wcf\system\menu\page\PageMenuItemProvider
	 */
	protected $provider = null;
	
	/**
	 * Returns the item provider for this page menu item.
	 * 
	 * @return wcf\system\menu\page\PageMenuItemProvider
	 */
	public function getProvider() {
		if ($this->provider === null) {
			if ($this->className) {
				if (!class_exists($this->className)) {
					throw new SystemException("Unable to find class '".$this->className."'");
				}
				if (!ClassUtil::isInstanceOf($this->className, 'wcf\system\menu\page\PageMenuItemProvider')) {
					throw new SystemException($this->className." should implement wcf\system\menu\page\PageMenuItemProvider");
				}
				
				$this->provider = new $this->className();
			}
			else {
				$this->provider = new DefaultPageMenuItemProvider();
			}
		}
		
		return $this->provider;
	}
	
	/**
	 * @see TreeMenuItem::getLink()
	 */
	public function getLink() {
		return LinkHandler::getInstance()->getLink($this->menuItemLink);
	}
}
