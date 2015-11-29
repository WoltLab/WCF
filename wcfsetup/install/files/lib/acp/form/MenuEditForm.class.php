<?php
namespace wcf\acp\form;
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
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
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
		$this->objectAction = new MenuAction(array($this->menuID), 'update', array('data' => array_merge($this->additionalFields, array(
			'title' => $this->title
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
			I18nHandler::getInstance()->setOptions('title', 1, $this->menu->title, 'wcf.menu.menu\d+');
			
			$this->title = $this->menu->title;
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
			'menuID' => $this->menuID,
			'menu' => $this->menu
		));
	}
}
