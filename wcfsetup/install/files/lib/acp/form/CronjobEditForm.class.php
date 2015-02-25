<?php
namespace wcf\acp\form;
use wcf\data\cronjob\Cronjob;
use wcf\data\cronjob\CronjobAction;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;

/**
 * Shows the cronjob edit form.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class CronjobEditForm extends CronjobAddForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.cronjob';
	
	/**
	 * cronjob id
	 * @var	integer
	 */
	public $cronjobID = 0;
	
	/**
	 * cronjob object
	 * @var	\wcf\data\cronjob\Cronjob
	 */
	public $cronjob = null;
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->cronjobID = intval($_REQUEST['id']);
		$this->cronjob = new Cronjob($this->cronjobID);
		if (!$this->cronjob->cronjobID) {
			throw new IllegalLinkException();
		}
		
		$this->packageID = $this->cronjob->packageID;
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		AbstractForm::save();
		
		$this->description = 'wcf.acp.cronjob.description.cronjob'.$this->cronjob->cronjobID;
		if (I18nHandler::getInstance()->isPlainValue('description')) {
			I18nHandler::getInstance()->remove($this->description);
			$this->description = I18nHandler::getInstance()->getValue('description');
		}
		else {
			I18nHandler::getInstance()->save('description', $this->description, 'wcf.acp.cronjob', $this->cronjob->packageID);
		}
		
		// update cronjob
		$data = array_merge($this->additionalFields, array(
			'className' => $this->className,
			'description' => $this->description,
			'startMinute' => $this->startMinute,
			'startHour' => $this->startHour,
			'startDom' => $this->startDom,
			'startMonth' => $this->startMonth,
			'startDow' => $this->startDow
		));
		
		$this->objectAction = new CronjobAction(array($this->cronjobID), 'update', array('data' => $data));
		$this->objectAction->executeAction();
		
		$this->saved();
		
		// show success
		WCF::getTPL()->assign(array(
			'success' => true
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			I18nHandler::getInstance()->setOptions('description', $this->cronjob->packageID, $this->cronjob->description, 'wcf.acp.cronjob.description.cronjob\d+');
			
			$this->className = $this->cronjob->className;
			$this->description = $this->cronjob->description;
			$this->startMinute = $this->cronjob->startMinute;
			$this->startHour = $this->cronjob->startHour;
			$this->startDom = $this->cronjob->startDom;
			$this->startMonth = $this->cronjob->startMonth;
			$this->startDow = $this->cronjob->startDow;
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables(!empty($_POST));
		
		WCF::getTPL()->assign(array(
			'cronjobID' => $this->cronjobID,
			'action' => 'edit'
		));
	}
}
