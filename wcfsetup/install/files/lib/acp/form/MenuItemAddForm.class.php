<?php
namespace wcf\acp\form;
use wcf\data\menu\item\MenuItem;
use wcf\data\menu\item\MenuItemAction;
use wcf\data\menu\item\MenuItemEditor;
use wcf\data\menu\item\MenuItemNodeTree;
use wcf\data\menu\Menu;
use wcf\data\page\Page;
use wcf\data\page\PageNodeTree;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the menu item add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class MenuItemAddForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.cms.menu.list';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.content.cms.canManageMenu'];
	
	/**
	 * menu id
	 * @var	integer
	 */
	public $menuID = 0;
	
	/**
	 * menu object
	 * @var	Menu
	 */
	public $menu = null;
	
	/**
	 * activation state
	 * @var	boolean
	 */
	public $isDisabled = false;
	
	/**
	 * internal link
	 * @var	boolean
	 */
	public $isInternalLink = true;
	
	/**
	 * page id
	 * @var	integer
	 */
	public $pageID = null;
	
	/**
	 * menu item title
	 * @var string
	 */
	public $title = '';
	
	/**
	 * external url
	 * @var	string
	 */
	public $externalURL = '';
	
	/**
	 * id of the parent menu item
	 * @var	integer
	 */
	public $parentItemID = null;
	
	/**
	 * show order
	 * @var	integer
	 */
	public $showOrder = 0;
	
	/**
	 * menu item node tree
	 * @var MenuItemNodeTree
	 */
	public $menuItems = null;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['menuID'])) $this->menuID = intval($_REQUEST['menuID']);
		$this->menu = new Menu($this->menuID);
		if (!$this->menu->menuID) {
			throw new IllegalLinkException();
		}
		
		I18nHandler::getInstance()->register('title');
		I18nHandler::getInstance()->register('externalURL');
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		I18nHandler::getInstance()->readValues();
		if (I18nHandler::getInstance()->isPlainValue('title')) $this->title = I18nHandler::getInstance()->getValue('title');
		if (I18nHandler::getInstance()->isPlainValue('externalURL')) $this->externalURL = I18nHandler::getInstance()->getValue('externalURL');
		
		if (isset($_POST['isDisabled'])) $this->isDisabled = true;
		$this->isInternalLink = false;
		if (isset($_POST['isInternalLink'])) $this->isInternalLink = (bool) $_POST['isInternalLink'];
		if (!empty($_POST['pageID'])) $this->pageID = intval($_POST['pageID']);
		if (!empty($_POST['parentItemID'])) $this->parentItemID = intval($_POST['parentItemID']);
		if (isset($_POST['showOrder'])) $this->showOrder = intval($_POST['showOrder']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		// validate menu item controller
		if ($this->isInternalLink) {
			$this->externalURL = '';
			
			if (!$this->pageID) {
				throw new UserInputException('pageID');
			}
			$page = new Page($this->pageID);
			if (!$page->pageID) {
				throw new UserInputException('pageID', 'invalid');
			}
		}
		else {
			$this->pageID = null;
			
			// validate external url
			if (!I18nHandler::getInstance()->validateValue('externalURL')) {
				throw new UserInputException('externalURL');
			}
		}
		
		// validate page menu item name
		if (!I18nHandler::getInstance()->validateValue('title')) {
			throw new UserInputException('title');
		}
		
		// validate parent menu item
		if ($this->parentItemID) {
			$parentMenuItem = new MenuItem($this->parentItemID);
			if (!$parentMenuItem->itemID || $parentMenuItem->menuID != $this->menuID) {
				throw new UserInputException('parentItemID', 'invalid');
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		$this->objectAction = new MenuItemAction(array(), 'create', array('data' => array_merge($this->additionalFields, array(
			'isDisabled' => ($this->isDisabled) ? 1 : 0,
			'title' => $this->title,
			'pageID' => $this->pageID,
			'externalURL' => $this->externalURL,
			'menuID' => $this->menuID,
			'parentItemID' => $this->parentItemID,
			'showOrder' => $this->showOrder,
			'identifier' => StringUtil::getRandomID()
		))));
		$this->objectAction->executeAction();
		
		$returnValues = $this->objectAction->getReturnValues();
		$menuItem = $returnValues['returnValues'];
		
		// set generic identifier
		$data = array(
			'identifier' => 'com.woltlab.wcf.generic'.$menuItem->itemID
		);
		if (!I18nHandler::getInstance()->isPlainValue('title')) {
			I18nHandler::getInstance()->save('title', 'wcf.menu.item.title'.$menuItem->itemID, 'wcf.menu');
			$data['title'] = 'wcf.menu.item.title'.$menuItem->itemID;
		}
		if (!I18nHandler::getInstance()->isPlainValue('externalURL')) {
			I18nHandler::getInstance()->save('externalURL', 'wcf.menu.item.externalURL'.$menuItem->itemID, 'wcf.menu');
			$data['externalURL'] = 'wcf.menu.item.externalURL'.$menuItem->itemID;
		}
		
		// update values
		$menuItemEditor = new MenuItemEditor($menuItem);
		$menuItemEditor->update($data);
		
		// call saved event
		$this->saved();
		
		// show success
		WCF::getTPL()->assign('success', true);
		
		// reset variables
		$this->isDisabled = $this->isInternalLink = false;
		$this->pageID = $this->parentItemID = null;
		$this->externalURL = $this->title = '';
		$this->showOrder = 0;
		
		I18nHandler::getInstance()->reset();
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
	
		$this->menuItems = new MenuItemNodeTree($this->menuID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables();
		$pageNodeList = new PageNodeTree();
		
		WCF::getTPL()->assign(array(
			'action' => 'add',
			'menuID' => $this->menuID,
			'menu' => $this->menu,
			'isDisabled' => $this->isDisabled,
			'isInternalLink' => $this->isInternalLink,
			'pageID' => $this->pageID,
			'title' => $this->title,
			'externalURL' => $this->externalURL,
			'parentItemID' => $this->parentItemID,
			'showOrder' => $this->showOrder,
			'menuItemNodeList' => $this->menuItems->getNodeList(),
			'pageNodeList' => $pageNodeList->getNodeList()
		));
	}
}
