<?php
namespace wcf\acp\form;
use wcf\data\cronjob\Cronjob;
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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class CronjobAddForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.cronjob.add';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.management.canManageCronjob'];
	
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
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		I18nHandler::getInstance()->register('description');
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		I18nHandler::getInstance()->readValues();
		
		if (isset($_POST['className'])) $this->className = StringUtil::trim($_POST['className']);
		if (isset($_POST['description'])) $this->description = StringUtil::trim($_POST['description']);
		if (isset($_POST['startMinute'])) $this->startMinute = str_replace(' ', '', $_POST['startMinute']);
		if (isset($_POST['startHour'])) $this->startHour = str_replace(' ', '', $_POST['startHour']);
		if (isset($_POST['startDom'])) $this->startDom = str_replace(' ', '', $_POST['startDom']);
		if (isset($_POST['startMonth'])) $this->startMonth = str_replace(' ', '', $_POST['startMonth']);
		if (isset($_POST['startDow'])) $this->startDow = str_replace(' ', '', $_POST['startDow']);
	}
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		// save cronjob
		$data = array_merge($this->additionalFields, [
			'className' => $this->className,
			'packageID' => $this->packageID,
			'cronjobName' => 'com.woltlab.wcf.cronjob',
			'description' => $this->description,
			'startMinute' => $this->startMinute,
			'startHour' => $this->startHour,
			'startDom' => $this->startDom,
			'startMonth' => $this->startMonth,
			'startDow' => $this->startDow
		]);
		
		$this->objectAction = new CronjobAction([], 'create', ['data' => $data]);
		/** @var Cronjob $cronjob */
		$cronjob = $this->objectAction->executeAction()['returnValues'];
		$cronjobID = $cronjob->cronjobID;
		
		// update `cronjobName`
		$data = ['cronjobName' => 'com.woltlab.wcf.cronjob' . $cronjobID];
		
		if (!I18nHandler::getInstance()->isPlainValue('description')) {
			I18nHandler::getInstance()->save('description', 'wcf.acp.cronjob.description.cronjob'.$cronjobID, 'wcf.acp.cronjob', $this->packageID);
			
			// update group name
			$data['description'] = 'wcf.acp.cronjob.description.cronjob' . $cronjobID;
		}
		
		$cronjobEditor = new CronjobEditor($cronjob);
		$cronjobEditor->update($data);
		
		$this->saved();
		
		// reset values
		$this->className = $this->description = '';
		$this->startMinute = $this->startHour = $this->startDom = $this->startMonth = $this->startDow = '*';
		I18nHandler::getInstance()->reset();
		
		// show success.
		WCF::getTPL()->assign([
			'success' => true
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables();
		
		WCF::getTPL()->assign([
			'className' => $this->className,
			'description' => $this->description,
			'startMinute' => $this->startMinute,
			'startHour' => $this->startHour,
			'startDom' => $this->startDom,
			'startMonth' => $this->startMonth,
			'startDow' => $this->startDow,
			'action' => 'add'
		]);
	}
}
