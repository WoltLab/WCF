<?php
namespace wcf\acp\form;
use wcf\form\AbstractForm;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\package\PackageUpdateDispatcher;
use wcf\system\WCF;
use wcf\system\WCFACP;

/**
 * Shows the package update confirmation form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class PackageUpdateForm extends AbstractForm {
	/**
	 * @see	wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.package';
	
	/**
	 * @see	wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.package.canUpdatePackage', 'admin.system.package.canInstallPackage');
	
	/**
	 * list of packages to update
	 * @var	array<string>
	 */
	public $updates = array();
	
	/**
	 * list with data of excluded packages
	 * @var	array<array>
	 */
	public $excludedPackages = array();
	
	/**
	 * list with data of packages which will be installed
	 * @var	array<array>
	 */
	public $packageInstallationStack = array();
	
	/**
	 * scheduler for package update
	 * @var	wcf\system\package\PackageInstallationScheduler
	 */
	public $packageUpdate = null;
	
	/**
	 * @see	wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_POST['updates']) && is_array($_POST['updates'])) $this->updates = $_POST['updates'];
	}
	
	/**
	 * @see	wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (empty($this->updates)) {
			throw new UserInputException('updates');
		}
		
		// build update stack
		$this->packageUpdate = PackageUpdateDispatcher::getInstance()->prepareInstallation($this->updates, array(), isset($_POST['send']));
		try {
			$this->packageUpdate->buildPackageInstallationStack();
			$this->excludedPackages = $this->packageUpdate->getExcludedPackages();
			if (!empty($this->excludedPackages)) {
				throw new UserInputException('excludedPackages');
			}
		}
		catch (SystemException $e) {
			// show detailed error message
			throw new UserInputException('updates', $e);
		}
	}
	
	/**
	 * @see	wcf\form\IForm::save()
	 */
	public function save() {
		if (isset($_POST['send'])) {
			parent::save();
			
			// save stack
			$processNo = $this->packageUpdate->savePackageInstallationStack();
			$this->saved();
			
			// open queue
			PackageInstallationDispatcher::openQueue(0, $processNo);
		}
	}
	
	/**
	 * @see	wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		// get installation stack
		if ($this->packageInstallationStack !== null && $this->packageUpdate !== null) {
			$this->packageInstallationStack = $this->packageUpdate->getPackageInstallationStack();
		}
	}
	
	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'updates' => $this->updates,
			'packageInstallationStack' => $this->packageInstallationStack,
			'excludedPackages' => $this->excludedPackages
		));
	}
	
	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function show() {
		// check master password
		WCFACP::checkMasterPassword();
		
		parent::show();
	}
}
