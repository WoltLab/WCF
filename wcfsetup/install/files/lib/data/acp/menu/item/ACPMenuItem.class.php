<?php
namespace wcf\data\acp\menu\item;
use wcf\data\DatabaseObject;
use wcf\system\menu\ITreeMenuItem;
use wcf\system\request\LinkHandler;
use wcf\system\Regex;
use wcf\system\WCF;

/**
 * Represents an ACP menu item.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.menu.item
 * @category	Community Framework
 */
class ACPMenuItem extends DatabaseObject implements ITreeMenuItem {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'acp_menu_item';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'menuItemID';
	
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
	 * @see	\wcf\system\menu\ITreeMenuItem::getLink()
	 */
	public function getLink() {
		// external link
		if (!$this->menuItemController) {
			return WCF::getLanguage()->get($this->menuItemLink);
		}
		
		$this->parseController();
		
		$linkParameters = array(
			'application' => $this->application
		);
		
		// links of top option category menu items need the id of the option
		// category
		if ($this->parentMenuItem == 'wcf.acp.menu.link.option.category') {
			$linkParameters['id'] = $this->optionCategoryID;
		}
		
		return LinkHandler::getInstance()->getLink($this->controller, $linkParameters, WCF::getLanguage()->get($this->menuItemLink));
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
		return WCF::getLanguage()->get($this->menuItem);
	}
}
