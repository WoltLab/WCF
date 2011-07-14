<?php
namespace wcf\acp\form;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\package\PackageUpdateDispatcher;
use wcf\system\WCF;
use wcf\system\WCFACP;
use wcf\util\HeaderUtil;

/**
 * Shows the package update confirmation form.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class PackageUpdateForm extends ACPForm {
	public $templateName = 'packageUpdate';
	public $neededPermissions = array('admin.system.package.canUpdatePackage', 'admin.system.package.canInstallPackage');
	public $activeMenuItem = 'wcf.acp.menu.link.package';
	
	public $updates = array();
	public $excludedPackages = array();
	public $packageInstallationStack = array();
	public $packageUpdate = null;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_POST['updates']) && is_array($_POST['updates'])) $this->updates = $_POST['updates'];
	}
	
	/**
	 * @see	Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (!count($this->updates)) {
			throw new UserInputException('updates');
		}
		
		// build update stack
		$this->packageUpdate = PackageUpdateDispatcher::prepareInstallation($this->updates, array(), isset($_POST['send']));
		try {
			$this->packageUpdate->buildPackageInstallationStack();
			$this->excludedPackages = $this->packageUpdate->getExcludedPackages();
			if (count($this->excludedPackages)) {
				throw new UserInputException('excludedPackages');
			}
		}
		catch (SystemException $e) {
			// show detailed error message
			throw new UserInputException('updates', $e);
		}
	}
	
	/**
	 * @see	Form::save()
	 */
	public function save() {
		if (isset($_POST['send'])) {
			parent::save();
			
			// save stack
			$processNo = $this->packageUpdate->savePackageInstallationStack();
			$this->saved();
			
			// open queue
			HeaderUtil::redirect('index.php?page=Package&action=openQueue&processNo='.$processNo.''.SID_ARG_2ND_NOT_ENCODED);
			exit;
		}
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// get installation stack
		if ($this->packageInstallationStack !== null && $this->packageUpdate !== null) {
			$this->packageInstallationStack = $this->packageUpdate->getPackageInstallationStack();
		}
	}
	
	/**
	 * @see Page::assignVariables()
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
	 * @see Page::assignVariables()
	 */
	public function show() {
		// check master password
		WCFACP::checkMasterPassword();
		
		parent::show();
	}
}
