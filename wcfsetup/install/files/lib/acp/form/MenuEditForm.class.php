<?php
namespace wcf\acp\form;
use wcf\data\box\BoxAction;
use wcf\data\menu\Menu;
use wcf\data\menu\MenuAction;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;

/**
 * Shows the menu edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 * @since	3.0
 */
class MenuEditForm extends MenuAddForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.cms.menu.list';
	
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
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
	
		if (isset($_REQUEST['id'])) $this->menuID = intval($_REQUEST['id']);
		$this->menu = new Menu($this->menuID);
		if (!$this->menu->menuID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function validatePosition() {
		if ($this->menu->identifier != 'com.woltlab.wcf.MainMenu') {
			parent::validatePosition();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
	
		$this->title = 'wcf.menu.menu'.$this->menu->menuID;
		if (I18nHandler::getInstance()->isPlainValue('title')) {
			I18nHandler::getInstance()->remove($this->title);
			$this->title = I18nHandler::getInstance()->getValue('title');
		}
		else {
			I18nHandler::getInstance()->save('title', $this->title, 'wcf.menu', 1);
		}
	
		// update menu
		$this->objectAction = new MenuAction([$this->menuID], 'update', ['data' => array_merge($this->additionalFields, [
			'title' => $this->title
		])]);
		$this->objectAction->executeAction();
		
		// update box
		if ($this->menu->identifier != 'com.woltlab.wcf.MainMenu') {
			$boxAction = new BoxAction([$this->menu->getBox()->boxID], 'update', ['data' => array_merge($this->additionalFields, [
				'position' => $this->position,
				'visibleEverywhere' => ($this->visibleEverywhere) ? 1 : 0,
				'showHeader' => ($this->showHeader) ? 1 : 0,
				'showOrder' => $this->showOrder,
				'cssClassName' => $this->cssClassName
			]), 'pageIDs' => $this->pageIDs]);
			$boxAction->executeAction();
		}
		
		$this->saved();
	
		// show success
		WCF::getTPL()->assign([
			'success' => true
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			I18nHandler::getInstance()->setOptions('title', 1, $this->menu->title, 'wcf.menu.menu\d+');
			
			$this->title = $this->menu->title;
			$this->position = $this->menu->getBox()->position;
			$this->cssClassName = $this->menu->getBox()->cssClassName;
			$this->showOrder = $this->menu->getBox()->showOrder;
			$this->visibleEverywhere = $this->menu->getBox()->visibleEverywhere;
			$this->pageIDs = $this->menu->getBox()->getPageIDs();
			$this->showHeader = $this->menu->getBox()->showHeader;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables(!empty($_POST));
		
		WCF::getTPL()->assign([
			'action' => 'edit',
			'menuID' => $this->menuID,
			'menu' => $this->menu
		]);
	}
}
