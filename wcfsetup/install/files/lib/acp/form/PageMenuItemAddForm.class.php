<?php
namespace wcf\acp\form;
use wcf\data\page\menu\item\PageMenuItemAction;
use wcf\data\page\menu\item\PageMenuItemEditor;
use wcf\data\page\menu\item\PageMenuItemList;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\page\PageManager;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the page menu item add form.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class PageMenuItemAddForm extends AbstractForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.pageMenu.add';
	
	/**
	 * list of available parent menu items
	 * @var	\wcf\data\page\menu\item\PageMenuItemList
	 */
	public $availableParentMenuItems = null;
	
	/**
	 * activation state
	 * @var	boolean
	 */
	public $isDisabled = false;
	
	/**
	 * internal link
	 * @var	boolean
	 */
	public $isInternalLink = false;
	
	/**
	 * menu item controller
	 * @var	string
	 */
	public $menuItemController = '';
	
	/**
	 * menu item parameters
	 * @var	string
	 */
	public $menuItemParameters = '';
	
	/**
	 * menu item link
	 * @var	string
	 */
	public $menuItemLink = '';
	
	/**
	 * menu item position
	 * @var	string
	 */
	public $menuPosition = 'header';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.display.canManagePageMenu');
	
	/**
	 * page menu item name
	 * @var	string
	 */
	public $pageMenuItem = '';
	
	/**
	 * parent menu item
	 * @var	string
	 */
	public $parentMenuItem = '';
	
	/**
	 * show order
	 * @var	integer
	 */
	public $showOrder = 0;
	
	/**
	 * available page object types
	 * @var	array<\wcf\data\object\type\ObjectType>
	 */
	public $pageObjectTypes = array();
	
	/**
	 * id of the page object type that belongs to the menu item's controller
	 * @var	integer
	 */
	public $menuItemPage = 0;
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		I18nHandler::getInstance()->register('menuItemLink');
		I18nHandler::getInstance()->register('pageMenuItem');
		
		$this->readAvailableParentMenuItems();
		
		$this->pageObjectTypes = PageManager::getInstance()->getObjectTypes();
	}
	
	/**
	 * Prepares list object to read available parent menu items.
	 */
	protected function initAvailableParentMenuItems() {
		$this->availableParentMenuItems = new PageMenuItemList();
		$this->availableParentMenuItems->getConditionBuilder()->add("page_menu_item.parentMenuItem = ''");
		$this->availableParentMenuItems->getConditionBuilder()->add('page_menu_item.menuPosition = ?', array('header'));
		$this->availableParentMenuItems->sqlOrderBy = "page_menu_item.showOrder ASC";
	}
	
	/**
	 * Reads a list of available parent menu items.
	 */
	protected function readAvailableParentMenuItems() {
		$this->initAvailableParentMenuItems();
		
		$this->availableParentMenuItems->readObjects();
	}
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		I18nHandler::getInstance()->readValues();
		if (I18nHandler::getInstance()->isPlainValue('menuItemLink')) $this->menuItemLink = I18nHandler::getInstance()->getValue('menuItemLink');
		if (I18nHandler::getInstance()->isPlainValue('pageMenuItem')) $this->pageMenuItem = I18nHandler::getInstance()->getValue('pageMenuItem');
		
		if (isset($_POST['isDisabled'])) $this->isDisabled = true;
		if (isset($_POST['isInternalLink'])) $this->isInternalLink = (bool) $_POST['isInternalLink'];
		if (isset($_POST['menuItemController'])) $this->menuItemController = StringUtil::trim($_POST['menuItemController']);
		if (isset($_POST['menuItemPage'])) $this->menuItemPage = intval($_POST['menuItemPage']);
		if (isset($_POST['menuItemParameters'])) $this->menuItemParameters = StringUtil::trim($_POST['menuItemParameters']);
		if (isset($_POST['menuPosition'])) $this->menuPosition = StringUtil::trim($_POST['menuPosition']);
		if (isset($_POST['parentMenuItem'])) $this->parentMenuItem = StringUtil::trim($_POST['parentMenuItem']);
		if (isset($_POST['showOrder'])) $this->showOrder = intval($_POST['showOrder']);
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		// validate menu position
		if (empty($this->menuPosition)) {
			throw new UserInputException('menuPosition');
		}
		else if ($this->menuPosition != 'header' && $this->menuPosition != 'footer') {
			throw new UserInputException('menuPosition', 'notValid');
		}
		
		// validate menu item controller
		if ($this->isInternalLink) {
			if ($this->menuItemPage) {
				$valid = false;
				foreach ($this->pageObjectTypes as $page) {
					if ($page->objectTypeID == $this->menuItemPage) {
						$this->menuItemController = $page->className;
						$valid = true;
						break;
					}
				}
				
				if (!$valid) {
					throw new UserInputException('menuItemPage', 'noValidSelection');
				}
			}
			else if (empty($this->menuItemController)) {
				throw new UserInputException('menuItemPage');
			}
			
			if (!class_exists($this->menuItemController)) {
				throw new UserInputException('menuItemController', 'notValid');
			}
			
			// validate menu item link
			if (!I18nHandler::getInstance()->validateValue('menuItemLink', false, true)) {
				throw new UserInputException('menuItemLink');
			}
		}
		else {
			$this->menuItemController = '';
			
			// validate menu item link
			if (!I18nHandler::getInstance()->validateValue('menuItemLink')) {
				throw new UserInputException('menuItemLink');
			}
		}
		
		// validate page menu item name
		if (!I18nHandler::getInstance()->validateValue('pageMenuItem', true)) {
			throw new UserInputException('pageMenuItem', 'multilingual');
		}
		
		// validate parent menu item
		if ($this->menuPosition == 'footer') {
			$this->parentMenuItem = '';
		}
		else if (!empty($this->parentMenuItem)) {
			$valid = false;
			foreach ($this->availableParentMenuItems as $menuItem) {
				if ($menuItem->menuItem == $this->parentMenuItem) {
					$valid = true;
					break;
				}
			}
			
			if (!$valid) {
				throw new UserInputException('parentMenuItem', 'notValid');
			}
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		$this->objectAction = new PageMenuItemAction(array(), 'create', array('data' => array_merge($this->additionalFields, array(
			'isDisabled' => ($this->isDisabled) ? 1 : 0,
			'menuItem' => $this->pageMenuItem,
			'menuItemController' => $this->menuItemController,
			'menuItemLink' => ($this->menuItemController ? $this->menuItemParameters : $this->menuItemLink),
			'menuPosition' => $this->menuPosition,
			'parentMenuItem' => $this->parentMenuItem,
			'showOrder' => $this->showOrder,
			'menuItem' => StringUtil::getRandomID()
		))));
		$this->objectAction->executeAction();
		
		$returnValues = $this->objectAction->getReturnValues();
		$menuItem = $returnValues['returnValues'];
		$data = array();
		if (!I18nHandler::getInstance()->isPlainValue('menuItemLink')) {
			I18nHandler::getInstance()->save('menuItemLink', 'wcf.page.menuItemLink'.$menuItem->menuItemID, 'wcf.page');
			$data['menuItemLink'] = 'wcf.page.menuItemLink'.$menuItem->menuItemID;
		}
		
		// save menu item
		I18nHandler::getInstance()->save('pageMenuItem', 'wcf.page.menuItem'.$menuItem->menuItemID, 'wcf.page');
		$data['menuItem'] = 'wcf.page.menuItem'.$menuItem->menuItemID;
		
		// update i18n values
		$menuItemEditor = new PageMenuItemEditor($menuItem);
		$menuItemEditor->update($data);
		PageMenuItemEditor::resetCache();
		
		// call saved event
		$this->saved();
		
		// show success
		WCF::getTPL()->assign('success', true);
		
		// reset variables
		$this->isDisabled = $this->isInternalLink = false;
		$this->menuPosition = 'header';
		$this->menuItemController = $this->menuItemLink = $this->pageMenuItem = $this->parentMenuItem = $this->menuItemParameters = '';
		$this->showOrder = 0;
		
		I18nHandler::getInstance()->reset();
		
		// reload parent menu items
		if ($menuItem->menuPosition == 'header' && $menuItem->parentMenuItem == '') {
			$this->readAvailableParentMenuItems();
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables();
		
		WCF::getTPL()->assign(array(
			'action' => 'add',
			'availableParentMenuItems' => $this->availableParentMenuItems,
			'isDisabled' => $this->isDisabled,
			'isInternalLink' => $this->isInternalLink,
			'menuItemController' => $this->menuItemPage ? '' : $this->menuItemController,
			'menuItemParameters' => $this->menuItemParameters,
			'menuItemLink' => $this->menuItemLink,
			'menuItemPage' => $this->menuItemPage,
			'menuPosition' => $this->menuPosition,
			'pages' => PageManager::getInstance()->getSelection(),
			'pageMenuItem' => $this->pageMenuItem,
			'parentMenuItem' => $this->parentMenuItem,
			'showOrder' => $this->showOrder
		));
	}
}
