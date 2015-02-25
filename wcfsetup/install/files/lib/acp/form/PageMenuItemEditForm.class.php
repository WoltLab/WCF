<?php
namespace wcf\acp\form;
use wcf\data\page\menu\item\PageMenuItem;
use wcf\data\page\menu\item\PageMenuItemAction;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;

/**
 * Shows the page menu item edit form.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class PageMenuItemEditForm extends PageMenuItemAddForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.pageMenu';
	
	/**
	 * page menu item object
	 * @var	\wcf\data\page\menu\item\PageMenuItem
	 */
	public $menuItem = null;
	
	/**
	 * menu item id
	 * @var	integer
	 */
	public $menuItemID = 0;
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		if (isset($_REQUEST['id'])) $this->menuItemID = intval($_REQUEST['id']);
		$this->menuItem = new PageMenuItem($this->menuItemID);
		if (!$this->menuItem->menuItemID) {
			throw new IllegalLinkException();
		}
		
		parent::readParameters();
	}
	
	/**
	 * @see	\wcf\acp\form\PageMenuItemAddForm::initAvailableParentMenuItems()
	 */
	protected function initAvailableParentMenuItems() {
		parent::initAvailableParentMenuItems();
		
		// remove current item as valid parent menu item
		$this->availableParentMenuItems->getConditionBuilder()->add("page_menu_item.menuItem <> ?", array($this->menuItem->menuItem));
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		I18nHandler::getInstance()->setOptions('menuItemLink', PACKAGE_ID, $this->menuItem->menuItemLink, 'wcf.page.menuItemLink\d+');
		I18nHandler::getInstance()->setOptions('pageMenuItem', PACKAGE_ID, $this->menuItem->menuItem, 'wcf.page.menuItem\d+');
		
		if (empty($_POST)) {
			$this->isDisabled = ($this->menuItem->isDisabled) ? true : false;
			$this->isInternalLink = ($this->menuItem->menuItemController) ? true : false;
			$this->menuItemController = $this->menuItem->menuItemController;
			if ($this->isInternalLink) {
				$this->menuItemParameters = $this->menuItem->menuItemLink;
			}
			else {
				$this->menuItemLink = $this->menuItem->menuItemLink;
			}
			$this->menuPosition = $this->menuItem->menuPosition;
			$this->pageMenuItem = $this->menuItem->menuItem;
			$this->parentMenuItem = $this->menuItem->parentMenuItem;
			$this->showOrder = $this->menuItem->showOrder;
			
			foreach ($this->pageObjectTypes as $page) {
				if ($page->className == $this->menuItemController) {
					$this->menuItemPage = $page->objectTypeID;
				}
			}
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		AbstractForm::save();
		
		// save menu item
		I18nHandler::getInstance()->save('pageMenuItem', $this->menuItem->menuItem, 'wcf.page');
		
		// save menu item link
		$this->menuItemLink = 'wcf.page.menuItemLink'.$this->menuItem->menuItemID;
		if (I18nHandler::getInstance()->isPlainValue('menuItemLink')) {
			I18nHandler::getInstance()->remove($this->menuItemLink);
			$this->menuItemLink= I18nHandler::getInstance()->getValue('menuItemLink');
		}
		else {
			I18nHandler::getInstance()->save('menuItemLink', $this->menuItemLink, 'wcf.page');
		}
		
		// save menu item
		$this->objectAction = new PageMenuItemAction(array($this->menuItem), 'update', array('data' => array_merge($this->additionalFields, array(
			'isDisabled' => ($this->isDisabled) ? 1 : 0,
			'menuItemController' => $this->menuItemController,
			'menuItemLink' => ($this->menuItemController ? $this->menuItemParameters : $this->menuItemLink),
			'parentMenuItem' => ($this->menuPosition == 'header' ? $this->parentMenuItem : ''),
			'menuPosition' => $this->menuPosition,
			'showOrder' => $this->showOrder
		))));
		$this->objectAction->executeAction();
		
		// update children
		if ($this->menuItem->menuPosition == 'header' && $this->menuPosition != 'header') {
			$sql = "UPDATE	wcf".WCF_N."_page_menu_item
				SET	parentMenuItem = ''
				WHERE	parentMenuItem = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($this->menuItem->menuItem));
		}
		$this->saved();
		
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables(!empty($_POST));
		
		WCF::getTPL()->assign(array(
			'action' => 'edit',
			'menuItem' => $this->menuItem,
			'menuItemID' => $this->menuItemID
		));
	}
}
