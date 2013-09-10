<?php
namespace wcf\data\user\menu\item;
use wcf\data\ProcessibleDatabaseObject;
use wcf\system\menu\user\DefaultUserMenuItemProvider;
use wcf\system\menu\ITreeMenuItem;
use wcf\system\request\LinkHandler;
use wcf\system\Regex;
use wcf\system\WCF;

/**
 * Represents an user menu item.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.menu.item
 * @category	Community Framework
 */
class UserMenuItem extends ProcessibleDatabaseObject implements ITreeMenuItem {
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'user_menu_item';
	
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'menuItemID';
	
	/**
	 * @see	wcf\data\ProcessibleDatabaseObject::$processorInterface
	 */
	protected static $processorInterface = 'wcf\system\menu\user\IUserMenuItemProvider';
	
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
	 * @see	wcf\data\ProcessibleDatabaseObject::getProcessor()
	 */
	public function getProcessor() {
		if (parent::getProcessor() === null) {
			$this->processor = new DefaultUserMenuItemProvider($this);
		}
		
		return $this->processor;
	}
	
	/**
	 * @see	wcf\system\menu\ITreeMenuItem::getLink()
	 */
	public function getLink() {
		// external link
		if (!$this->menuItemController) {
			return $this->menuItemLink;
		}
		
		$this->parseController();
		return LinkHandler::getInstance()->getLink($this->controller, array('application' => $this->application), $this->menuItemLink);
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
