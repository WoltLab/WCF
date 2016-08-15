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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Acp\Menu\Item
 *
 * @property-read	integer		$menuItemID		unique id of the ACP menu item
 * @property-read	integer		$packageID		id of the package which delivers the ACP menu item
 * @property-read	string		$menuItem		textual identifier of the ACP menu item
 * @property-read	string		$parentMenuItem		textual identifier of the ACP menu item's parent menu item or empty if it has no parent menu item
 * @property-read	string		$menuItemController	class name of the ACP menu item's controller used to generate menu item link
 * @property-read	string		$menuItemLink		additional part of the ACP menu item link if `$menuItemController` is set, external link or name of language item which contains the external link
 * @property-read	integer		$showOrder		position of the ACP menu item in relation to its siblings
 * @property-read	string		$permissions		comma separated list of user group permissions of which the active user needs to have at least one to see the ACP menu item
 * @property-read	string		$options		comma separated list of options of which at least one needs to be enabled for the ACP menu item to be shown
 * @property-read	string		$icon			FontAwesome CSS class name for ACP menu items on the first or third level
 */
class ACPMenuItem extends DatabaseObject implements ITreeMenuItem {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'acp_menu_item';
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function getLink() {
		// external link
		if (!$this->menuItemController) {
			return WCF::getLanguage()->get($this->menuItemLink);
		}
		
		$this->parseController();
		
		$linkParameters = [
			'application' => $this->application
		];
		
		// links of top option category menu items need the id of the option
		// category
		if ($this->parentMenuItem == 'wcf.acp.menu.link.option.category') {
			/** @noinspection PhpUndefinedFieldInspection */
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
