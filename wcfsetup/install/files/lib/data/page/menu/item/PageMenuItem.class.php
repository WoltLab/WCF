<?php
namespace wcf\data\page\menu\item;
use wcf\data\ProcessibleDatabaseObject;
use wcf\system\application\ApplicationHandler;
use wcf\system\menu\page\DefaultPageMenuItemProvider;
use wcf\system\menu\ITreeMenuItem;
use wcf\system\request\LinkHandler;

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
	 * @see wcf\data\ProcessibleDatabaseObject::getProcessor()
	 */
	public function getProcessor() {
		if (parent::getProcessor() === null) {
			$this->processor = new DefaultPageMenuItemProvider($this);
		}
		
		return $this->processor;
	}
	
	/**
	 * @see wcf\system\menu\ITreeMenuItem::getLink()
	 */
	public function getLink() {
		$abbreviation = ApplicationHandler::getInstance()->getAbbreviation($this->packageID);
		
		$parameters = array();
		if ($abbreviation) {
			$parameters['application'] = $abbreviation;
		}
		
		return LinkHandler::getInstance()->getLink(null, $parameters, $this->menuItemLink);
	}
}
