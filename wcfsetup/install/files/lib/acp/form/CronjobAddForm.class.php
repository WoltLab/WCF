<?php
namespace wcf\acp\form;
use wcf\data\cronjob\CronjobAction;
use wcf\data\cronjob\CronjobEditor;
use wcf\form\AbstractForm;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;
use wcf\util\CronjobUtil;
use wcf\util\StringUtil;

/**
 * Shows the cronjob add form.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class CronjobAddForm extends AbstractForm {
	/**
	 * @see	wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.cronjob.add';
	
	/**
	 * @see	wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.canManageCronjob');
	
	/**
	 * cronjob class name
	 * @var	string
	 */
	public $className = '';
	
	/**
	 * cronjob package id
	 * @var	integer
	 */
	public $packageID = PACKAGE_ID;
	
	/**
	 * cronjob description
	 * @var	string
	 */
	public $description = '';
	
	/**
	 * execution time (min)
	 * @var	string
	 */
	public $startMinute = '*';
	
	/**
	 * execution time (hour)
	 * @var	string
	 */
	public $startHour = '*';
	
	/**
	 * execution time (day of month)
	 * @var	string
	 */
	public $startDom = '*';
	
	/**
	 * execution time (month)
	 * @var	string
	 */
	public $startMonth = '*';
	
	/**
	 * execution time (day of week)
	 * @var	string
	 */
	public $startDow = '*';
	
	/**
	 * @see	wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		I18nHandler::getInstance()->register('description');
	}
	
	/**
	 * @see	wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		I18nHandler::getInstance()->readValues();
		
		if (isset($_POST['className'])) $this->className = StringUtil::trim($_POST['className']);
		if (isset($_POST['description'])) $this->description = StringUtil::trim($_POST['description']);
		if (isset($_POST['startMinute'])) $this->startMinute = StringUtil::replace(' ', '', $_POST['startMinute']);
		if (isset($_POST['startHour'])) $this->startHour = StringUtil::replace(' ', '', $_POST['startHour']);
		if (isset($_POST['startDom'])) $this->startDom = StringUtil::replace(' ', '', $_POST['startDom']);
		if (isset($_POST['startMonth'])) $this->startMonth = StringUtil::replace(' ', '', $_POST['startMonth']);
		if (isset($_POST['startDow'])) $this->startDow = StringUtil::replace(' ', '', $_POST['startDow']);
	}
	
	/**
	 * @see	wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		// validate class name
		if (empty($this->className)) {
			throw new UserInputException('className');
		}
		
		if (!class_exists($this->className)) {
			throw new UserInputException('className', 'doesNotExist');
		}
		
		// validate description
		if (!I18nHandler::getInstance()->validateValue('description')) {
			if (I18nHandler::getInstance()->isPlainValue('description')) {
				throw new UserInputException('description');
			}
			else {
				throw new UserInputException('description', 'multilingual');
			}
		}
		
		try {
			CronjobUtil::validate($this->startMinute, $this->startHour, $this->startDom, $this->startMonth, $this->startDow);
		} 
		catch (SystemException $e) {
			// extract field name
			$fieldName = '';
			if (preg_match("/cronjob attribute '(.*)'/", $e->getMessage(), $match)) {
				$fieldName = $match[1];
			}
			
			throw new UserInputException($fieldName, 'notValid');
		}
	}
	
	/**
	 * @see	wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// save cronjob
		$data = array(
			'className' => $this->className,
			'packageID' => $this->packageID,
			'description' => $this->description,
			'startMinute' => $this->startMinute,
			'startHour' => $this->startHour,
			'startDom' => $this->startDom,
			'startMonth' => $this->startMonth,
			'startDow' => $this->startDow
		);
		
		$this->objectAction = new CronjobAction(array(), 'create', array('data' => $data));
		$this->objectAction->executeAction();
		
		if (!I18nHandler::getInstance()->isPlainValue('description')) {
			$returnValues = $this->objectAction->getReturnValues();
			$cronjobID = $returnValues['returnValues']->cronjobID;
			I18nHandler::getInstance()->save('description', 'wcf.acp.cronjob.description.cronjob'.$cronjobID, 'wcf.acp.cronjob', $this->packageID);
			
			// update group name
			$cronjobEditor = new CronjobEditor($returnValues['returnValues']);
			$cronjobEditor->update(array(
				'description' => 'wcf.acp.cronjob.description.cronjob'.$cronjobID
			));
		}
		
		$this->saved();
		
		// reset values
		$this->className = $this->description = '';
		$this->startMinute = $this->startHour = $this->startDom = $this->startMonth = $this->startDow = '*';
		
		// show success.
		WCF::getTPL()->assign(array(
			'success' => true
		));
	}
	
	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables();
		
		WCF::getTPL()->assign(array(
			'className' => $this->className,
			'description' => $this->description,
			'startMinute' => $this->startMinute,
			'startHour' => $this->startHour,
			'startDom' => $this->startDom,
			'startMonth' => $this->startMonth,
			'startDow' => $this->startDow,
			'action' => 'add'
		));
	}
}
