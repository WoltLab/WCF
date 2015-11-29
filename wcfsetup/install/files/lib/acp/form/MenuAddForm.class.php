<?php
namespace wcf\acp\form;
use wcf\data\menu\MenuAction;
use wcf\data\menu\MenuEditor;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;

/**
 * Shows the menu add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class MenuAddForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.cms.menu.add';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.content.cms.canManageMenu'];
	
	/**
	 * menu title
	 * @var string
	 */
	public $title = '';
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
	
		I18nHandler::getInstance()->register('title');
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		I18nHandler::getInstance()->readValues();
		
		if (I18nHandler::getInstance()->isPlainValue('title')) $this->title = I18nHandler::getInstance()->getValue('title');
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		// validate menu title
		if (!I18nHandler::getInstance()->validateValue('title')) {
			if (I18nHandler::getInstance()->isPlainValue('title')) {
				throw new UserInputException('title');
			}
			else {
				throw new UserInputException('title', 'multilingual');
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		// save label
		$this->objectAction = new MenuAction(array(), 'create', array('data' => array_merge($this->additionalFields, array(
			'title' => $this->title,
			'packageID' => 1,
			'identifier' => ''
		))));
		$returnValues = $this->objectAction->executeAction();
		// set generic identifier
		$menuEditor = new MenuEditor($returnValues['returnValues']);
		$menuEditor->update(array(
			'identifier' => 'com.woltlab.wcf.generic'.$menuEditor->menuID
		));
		// save i18n
		if (!I18nHandler::getInstance()->isPlainValue('title')) {
			I18nHandler::getInstance()->save('title', 'wcf.menu.menu'.$menuEditor->menuID, 'wcf.menu', 1);
				
			// update title
			$menuEditor->update(array(
				'title' => 'wcf.menu.menu'.$menuEditor->menuID
			));
		}
		$this->saved();
		
		// reset values
		$this->title = '';
		
		// show success
		WCF::getTPL()->assign(array(
			'success' => true
		));
		
		I18nHandler::getInstance()->reset();
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables();
		
		WCF::getTPL()->assign(array(
			'action' => 'add',
			'title' => 'title'
		));
	}
}
