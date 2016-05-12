<?php
namespace wcf\acp\form;
use wcf\data\menu\item\MenuItem;
use wcf\data\menu\item\MenuItemAction;
use wcf\data\menu\Menu;
use wcf\data\page\PageNodeTree;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\language\I18nHandler;
use wcf\system\page\handler\ILookupPageHandler;
use wcf\system\WCF;

/**
 * Shows the menu item edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 * @since	2.2
 */
class MenuItemEditForm extends MenuItemAddForm {
	/**
	 * menu item id
	 * @var	integer
	 */
	public $itemID = 0;
	
	/**
	 * menu object
	 * @var	Menu
	 */
	public $menuItem = null;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		AbstractForm::readParameters();
		
		if (isset($_REQUEST['id'])) $this->itemID = intval($_REQUEST['id']);
		$this->menuItem = new MenuItem($this->itemID);
		if (!$this->menuItem->itemID) {
			throw new IllegalLinkException();
		}
		
		$this->menu = new Menu($this->menuItem->menuID);
		$this->menuID = $this->menu->menuID;
		
		I18nHandler::getInstance()->register('title');
		I18nHandler::getInstance()->register('externalURL');
		
		$this->pageNodeList = (new PageNodeTree())->getNodeList();
		
		// fetch page handlers
		foreach ($this->pageNodeList as $pageNode) {
			$handler = $pageNode->getHandler();
			if ($handler !== null) {
				if ($handler instanceof ILookupPageHandler) {
					$this->pageHandlers[$pageNode->pageID] = $pageNode->requireObjectID;
				}
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		$this->title = 'wcf.menu.item.title'.$this->menuItem->itemID;
		if (I18nHandler::getInstance()->isPlainValue('title')) {
			I18nHandler::getInstance()->remove($this->title);
			$this->title = I18nHandler::getInstance()->getValue('title');
		}
		else {
			I18nHandler::getInstance()->save('title', $this->title, 'wcf.menu', 1);
		}
		$this->externalURL = 'wcf.menu.item.externalURL'.$this->menuItem->itemID;
		if (I18nHandler::getInstance()->isPlainValue('externalURL')) {
			I18nHandler::getInstance()->remove($this->externalURL);
			$this->externalURL = I18nHandler::getInstance()->getValue('externalURL');
		}
		else {
			I18nHandler::getInstance()->save('externalURL', $this->externalURL, 'wcf.menu', 1);
		}
		
		// update menu
		$this->objectAction = new MenuItemAction(array($this->itemID), 'update', array('data' => array_merge($this->additionalFields, array(
			'isDisabled' => ($this->isDisabled) ? 1 : 0,
			'title' => $this->title,
			'pageID' => $this->pageID,
			'pageObjectID' => ($this->pageObjectID ?: 0),
			'externalURL' => $this->externalURL,
			'parentItemID' => $this->parentItemID,
			'showOrder' => $this->showOrder
		))));
		$this->objectAction->executeAction();
		$this->saved();
		
		// show success
		WCF::getTPL()->assign(array(
			'success' => true
		));
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
	
		if (empty($_POST)) {
			I18nHandler::getInstance()->setOptions('title', 1, $this->menuItem->title, 'wcf.menu.item.title\d+');
			I18nHandler::getInstance()->setOptions('externalURL', 1, $this->menuItem->externalURL, 'wcf.menu.item.externalURL\d+');
			
			$this->parentItemID = $this->menuItem->parentItemID;
			$this->title = $this->menuItem->title;
			$this->pageID = $this->menuItem->pageID;
			$this->pageObjectID = $this->menuItem->pageObjectID;
			$this->externalURL = $this->menuItem->externalURL;
			$this->showOrder = $this->menuItem->showOrder;
			$this->isDisabled = $this->menuItem->isDisabled;
			if (!$this->pageID) {
				$this->isInternalLink = false;
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables(!empty($_POST));
		
		WCF::getTPL()->assign(array(
			'action' => 'edit',
			'itemID' => $this->itemID,
			'menuItem' => $this->menuItem
		));
	}
}
