<?php
namespace wcf\form;
use wcf\page\AbstractPage;
use wcf\system\event\EventHandler;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * This class provides default implementations for the Form interface.
 * This includes the default event listener for a form: readFormParameters, validate, save.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	form
 * @category	Community Framework
 */
abstract class AbstractForm extends AbstractPage implements IForm {
	/**
	 * active tab menu item
	 * @var	string
	 */
	public $activeTabMenuItem = '';
	
	/**
	 * name of error field
	 * @var	string
	 */
	public $errorField = '';
	
	/**
	 * error type
	 * @var	string
	 */
	public $errorType = '';
	
	/**
	 * database object action
	 * @var	\wcf\data\AbstractDatabaseObjectAction
	 */
	public $objectAction = null;
	
	/**
	 * additional fields
	 * @var	array<mixed>
	 */
	public $additionalFields = array();
	
	/**
	 * @see	\wcf\form\IForm::submit()
	 */
	public function submit() {
		// call submit event
		EventHandler::getInstance()->fireAction($this, 'submit');
		
		$this->readFormParameters();
		
		try {
			$this->validate();
			// no errors
			$this->save();
		}
		catch (UserInputException $e) {
			$this->errorField = $e->getField();
			$this->errorType = $e->getType();
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		// call readFormParameters event
		EventHandler::getInstance()->fireAction($this, 'readFormParameters');
		
		if (isset($_POST['activeTabMenuItem'])) $this->activeTabMenuItem = StringUtil::trim($_POST['activeTabMenuItem']);
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		// call validate event
		EventHandler::getInstance()->fireAction($this, 'validate');
		
		if (!isset($_POST['t']) || !WCF::getSession()->checkSecurityToken($_POST['t'])) {
			throw new UserInputException('__securityToken');
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		// call save event
		EventHandler::getInstance()->fireAction($this, 'save');
	}
	
	/**
	 * Calls the 'saved' event after the successful call of the save method.
	 * This functions won't called automatically. You must do this manually, if you inherit AbstractForm.
	 */
	protected function saved() {
		EventHandler::getInstance()->fireAction($this, 'saved');
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		if (!empty($_POST) || !empty($_FILES)) {
			$this->submit();
		}
		
		parent::readData();
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		// assign default variables
		WCF::getTPL()->assign(array(
			'activeTabMenuItem' => $this->activeTabMenuItem,
			'errorField' => $this->errorField,
			'errorType' => $this->errorType
		));
	}
}
