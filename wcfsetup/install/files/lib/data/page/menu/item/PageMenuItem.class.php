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
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.page.menu.item
 * @category	Community Framework
 */
class PageMenuItem extends ProcessibleDatabaseObject implements ITreeMenuItem {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'page_menu_item';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'menuItemID';
	
	/**
	 * @see	\wcf\data\ProcessibleDatabaseObject::$processorInterface
	 */
	protected static $processorInterface = 'wcf\system\menu\page\IPageMenuItemProvider';
	
	/**
	 * application abbreviation
	 * @var	string
	 */
	protected $application = '';
	
	/**
	 * menu item controller
	 * @var	string
	 */
	protected $controller = null;
	
	/**
	 * @see	\wcf\data\ProcessibleDatabaseObject::getProcessor()
	 */
	public function getProcessor() {
		if (parent::getProcessor() === null) {
			$this->processor = new DefaultPageMenuItemProvider($this);
		}
		
		return $this->processor;
	}
	
	/**
	 * @see	\wcf\system\menu\ITreeMenuItem::getLink()
	 */
	public function getLink() {
		// external link
		if (!$this->menuItemController) {
			return WCF::getLanguage()->get($this->menuItemLink);
		}
		
		$this->parseController();
		return LinkHandler::getInstance()->getLink($this->controller, array('application' => $this->application, 'forceFrontend' => true), WCF::getLanguage()->get($this->menuItemLink));
	}
	
	/**
	 * Returns true if current menu item may be set as landing page.
	 * 
	 * @return	boolean
	 */
	public function isValidLandingPage() {
		// item must be a top header menu item without parents
		if ($this->menuPosition != 'header' || $this->parentMenuItem) {
			return false;
		}
		
		// external links are not valid
		if (!$this->menuItemController) {
			return false;
		}
		
		// already is landing page
		if ($this->isLandingPage) {
			return false;
		}
		
		// disabled items cannot be a landing page
		if ($this->isDisabled) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Returns true if this item can be deleted.
	 * 
	 * @return	boolean
	 */
	public function canDelete() {
		if ($this->originIsSystem || $this->isLandingPage) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Returns true if this item can be disabled.
	 * 
	 * @return	boolean
	 */
	public function canDisable() {
		return ($this->isLandingPage ? false : true);
	}
	
	/**
	 * Returns application abbreviation.
	 * 
	 * @return	string
	 */
	public function getApplication() {
		$this->parseController();
		
		return $this->application;
	}
	
	/**
	 * Returns controller name.
	 * 
	 * @return	string
	 */
	public function getController() {
		$this->parseController();
		
		return $this->controller;
	}
	
	/**
	 * Parses controller name.
	 */
	protected function parseController() {
		if ($this->controller === null) {
			$this->controller = '';
			
			// resolve application and controller
			if ($this->menuItemController) {
				$parts = explode('\\', $this->menuItemController);
				$this->application = array_shift($parts);
				$menuItemController = array_pop($parts);
				
				// drop controller suffix
				$this->controller = Regex::compile('(Action|Form|Page)$')->replace($menuItemController, '');
			}
		}
	}
	
	/**
	 * Returns the menu item name.
	 * 
	 * @return	string
	 */
	public function __toString() {
		return WCF::getLanguage()->getDynamicVariable($this->menuItem);
	}
}
