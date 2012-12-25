<?php
namespace wcf\data\page\menu\item;
use wcf\data\ProcessibleDatabaseObject;
use wcf\system\menu\page\DefaultPageMenuItemProvider;
use wcf\system\menu\ITreeMenuItem;
use wcf\system\request\LinkHandler;
use wcf\system\Regex;
use wcf\system\WCF;

/**
 * Represents a page menu item.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.page.menu.item
 * @category	Community Framework
 */
class PageMenuItem extends ProcessibleDatabaseObject implements ITreeMenuItem {
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'page_menu_item';
	
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'menuItemID';
	
	/**
	 * @see	wcf\data\ProcessibleDatabaseObject::$processorInterface
	 */
	protected static $processorInterface = 'wcf\system\menu\page\IPageMenuItemProvider';
	
	/**
	 * @see	wcf\data\ProcessibleDatabaseObject::getProcessor()
	 */
	public function getProcessor() {
		if (parent::getProcessor() === null) {
			$this->processor = new DefaultPageMenuItemProvider($this);
		}
		
		return $this->processor;
	}
	
	/**
	 * @see	wcf\system\menu\ITreeMenuItem::getLink()
	 */
	public function getLink() {
		// external link
		if ($this->menuItemController === null) {
			return WCF::getLanguage()->get($this->menuItemLink);
		}
		
		// resolve application and controller
		$parts = explode('\\', $this->menuItemController);
		$abbreviation = array_shift($parts);
		$controller = array_pop($parts);
		
		// drop controller suffix
		$controller = Regex::compile('(Action|Form|Page)$')->replace($controller, '');
		
		return LinkHandler::getInstance()->getLink($controller, array('application' => $abbreviation), WCF::getLanguage()->get($this->menuItemLink));
	}
	
	/**
	 * Returns the menu item name.
	 * 
	 * @return	string
	 */
	public function __toString() {
		return WCF::getLanguage()->get($this->menuItem);
	}
}
