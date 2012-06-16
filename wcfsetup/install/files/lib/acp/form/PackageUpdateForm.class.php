<?php
namespace wcf\acp\form;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\package\PackageUpdateDispatcher;
use wcf\system\request\LinkHandler;
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
	/**
	 * @see wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.package.canUpdatePackage', 'admin.system.package.canInstallPackage');
	
	/**
	 * @see wcf\acp\form\ACPForm::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.package';
	
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
	 * @see wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_POST['updates']) && is_array($_POST['updates'])) $this->updates = $_POST['updates'];
	}
	
	/**
	 * @see wcf\form\IForm::validate()
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
	 * @see wcf\form\IForm::save()
	 */
	public function save() {
		if (isset($_POST['send'])) {
			parent::save();
			
			// save stack
			$processNo = $this->packageUpdate->savePackageInstallationStack();
			$this->saved();
			
			// open queue
			$url = LinkHandler::getInstance()->getLink('Package', array(), 'action=openQueue&processNo='.$processNo);
			HeaderUtil::redirect($url);
			exit;
		}
	}
	
	/**
	 * @see wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		// get installation stack
		if ($this->packageInstallationStack !== null && $this->packageUpdate !== null) {
			$this->packageInstallationStack = $this->packageUpdate->getPackageInstallationStack();
		}
	}
	
	/**
	 * @see wcf\page\IPage::assignVariables()
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
	 * @see wcf\page\IPage::assignVariables()
	 */
	public function show() {
		// check master password
		WCFACP::checkMasterPassword();
		
		parent::show();
	}
}
