<?php
namespace wcf\acp\form;
use wcf\data\cronjob\CronjobAction;
use wcf\data\package\Package;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\CronjobUtil;
use wcf\util\StringUtil;

/**
 * Shows the cronjobs add form.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class CronjobAddForm extends ACPForm {
	/**
	 * @see wcf\page\AbstractPage::$templateName
	 */
	public $templateName = 'cronjobAdd';
	
	/**
	 * @see wcf\acp\form\ACPForm::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.cronjobs.add';
	
	/**
	 * @see wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.cronjobs.canAddCronjob');
	
	/**
	 * cronjob class name
	 * @var string
	 */
	public $className = '';
	
	/**
	 * cronjob package id
	 * @var integer
	 */
	public $packageID = PACKAGE_ID;
	
	/**
	 * cronjob description
	 * @var string
	 */
	public $description = '';
	
	/**
	 * execution time (min)
	 * @var string
	 */
	public $startMinute = '*';
	
	/**
	 * execution time (hour)
	 * @var string
	 */
	public $startHour = '*';
	
	/**
	 * execution time (day of month)
	 * @var string
	 */
	public $startDom = '*';
	
	/**
	 * execution time (month)
	 * @var string
	 */
	public $startMonth = '*';
	
	/**
	 * execution time (day of week)
	 * @var string
	 */
	public $startDow = '*';
	
	/**
	 * @see wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['className'])) $this->className = StringUtil::trim($_POST['className']);
		if (isset($_POST['description'])) $this->description = StringUtil::trim($_POST['description']);
		if (isset($_POST['startMinute'])) $this->startMinute = StringUtil::replace(' ', '', $_POST['startMinute']);
		if (isset($_POST['startHour'])) $this->startHour = StringUtil::replace(' ', '', $_POST['startHour']);
		if (isset($_POST['startDom'])) $this->startDom = StringUtil::replace(' ', '', $_POST['startDom']);
		if (isset($_POST['startMonth'])) $this->startMonth = StringUtil::replace(' ', '', $_POST['startMonth']);
		if (isset($_POST['startDow'])) $this->startDow = StringUtil::replace(' ', '', $_POST['startDow']);
	}
	
	/**
	 * @see wcf\form\IForm::validate()
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
	 * @see wcf\form\IForm::save()
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
		
		$cronjobAction = new CronjobAction(array(), 'create', array('data' => $data));
		$cronjobAction->executeAction();
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
	 * @see wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
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
