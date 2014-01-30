<?php
namespace wcf\acp\form;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\data\package\installation\queue\PackageInstallationQueueEditor;
use wcf\data\package\Package;
use wcf\form\AbstractForm;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\package\PackageArchive;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\WCF;
use wcf\system\WCFACP;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * Shows the package install and update form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class PackageStartInstallForm extends AbstractForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.package.install';
	
	/**
	 * updated package object
	 * @var	\wcf\system\package\Package
	 */
	public $package = null;
	
	/**
	 * url to the package to download
	 * @var	string
	 */
	public $downloadPackage = '';
	
	/**
	 * data of the uploaded package
	 * @var	array<string>
	 */
	public $uploadPackage = '';
	
	/**
	 * archive of the installation/update package
	 * @var	\wcf\system\package\PackageArchive
	 */
	public $archive = null;
	
	/**
	 * package installation/update queue
	 * @var	\wcf\data\package\installation\queue\PackageInstallationQueue
	 */
	public $queue = null;
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['downloadPackage'])) $this->downloadPackage = StringUtil::trim($_POST['downloadPackage']);
		if (isset($_FILES['uploadPackage'])) $this->uploadPackage = $_FILES['uploadPackage'];
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (!empty($this->uploadPackage['name'])) {
			$this->validateUploadPackage();
		}
		else if (!empty($this->downloadPackage)) {
			$this->validateDownloadPackage();
		}
		else {
			throw new UserInputException('uploadPackage');
		}
	}
	
	/**
	 * Validates the upload package input.
	 */
	protected function validateUploadPackage() {
		$this->activeTabMenuItem = 'upload';
		
		if (empty($this->uploadPackage['tmp_name'])) {
			throw new UserInputException('uploadPackage', 'uploadFailed');
		}
		
		// get filename
		$this->uploadPackage['name'] = FileUtil::getTemporaryFilename('package_', preg_replace('!^.*(?=\.(?:tar\.gz|tgz|tar)$)!i', '', basename($this->uploadPackage['name'])));
		
		if (!@move_uploaded_file($this->uploadPackage['tmp_name'], $this->uploadPackage['name'])) {
			throw new UserInputException('uploadPackage', 'uploadFailed');
		}
		
		$this->archive = new PackageArchive($this->uploadPackage['name'], $this->package);
		$this->validateArchive('uploadPackage');
	}
	
	/**
	 * Validates the download package input.
	 */
	protected function validateDownloadPackage() {
		$this->activeTabMenuItem = 'upload';
		
		if (FileUtil::isURL($this->downloadPackage)) {
			// download package
			$this->archive = new PackageArchive($this->downloadPackage, $this->package);
			
			try {
				$this->downloadPackage = $this->archive->downloadArchive();
			}
			catch (SystemException $e) {
				throw new UserInputException('downloadPackage', 'downloadFailed');
			}
		}
		else {
			// probably local path
			if (!file_exists($this->downloadPackage)) {
				throw new UserInputException('downloadPackage', 'downloadFailed');
			}
			
			$this->archive = new PackageArchive($this->downloadPackage, $this->package);
		}
		
		$this->validateArchive('downloadPackage');
	}
	
	/**
	 * Validates the package archive.
	 * 
	 * @param	string		$type		upload or download package
	 */
	protected function validateArchive($type) {
		// try to open the archive
		try {
			// TODO: Exceptions thrown within openArchive() are discarded, resulting in
			// the meaningless message 'not a valid package'
			$this->archive->openArchive();
		}
		catch (SystemException $e) {
			throw new UserInputException($type, 'noValidPackage');
		}
		
		// validate php requirements
		$errors = PackageInstallationDispatcher::validatePHPRequirements($this->archive->getPhpRequirements());
		if (!empty($errors)) {
			WCF::getTPL()->assign('phpRequirements', $errors);
			throw new UserInputException($type, 'phpRequirements');
		}
		
		// try to find existing package
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_package
			WHERE	package = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->archive->getPackageInfo('name')));
		$row = $statement->fetchArray();
		if ($row !== false) {
			$this->package = new Package(null, $row);
		}
		
		// check update or install support
		if ($this->package !== null) {
			WCF::getSession()->checkPermissions(array('admin.system.package.canUpdatePackage'));
			$this->activeMenuItem = 'wcf.acp.menu.link.package';
			
			if (!$this->archive->isValidUpdate($this->package)) {
				throw new UserInputException($type, 'noValidUpdate');
			}
		}
		else {
			WCF::getSession()->checkPermissions(array('admin.system.package.canInstallPackage'));
			
			if (!$this->archive->isValidInstall()) {
				throw new UserInputException($type, 'noValidInstall');
			}
			else if ($this->archive->isAlreadyInstalled()) {
				throw new UserInputException($type, 'uniqueAlreadyInstalled');
			}
			else if ($this->archive->getPackageInfo('isApplication') && $this->archive->hasUniqueAbbreviation()) {
				throw new UserInputException($type, 'noUniqueAbbrevation');
			}
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// get new process no
		$processNo = PackageInstallationQueue::getNewProcessNo();
		
		// obey foreign key
		$packageID = ($this->package) ? $this->package->packageID : null;
		
		// insert queue
		$isApplication = $this->archive->getPackageInfo('isApplication');
		$this->queue = PackageInstallationQueueEditor::create(array(
			'processNo' => $processNo,
			'userID' => WCF::getUser()->userID,
			'package' => $this->archive->getPackageInfo('name'),
			'packageName' => $this->archive->getLocalizedPackageInfo('packageName'),
			'packageID' => $packageID,
			'archive' => (!empty($this->uploadPackage['tmp_name']) ? $this->uploadPackage['name'] : $this->downloadPackage),
			'action' => ($this->package != null ? 'update' : 'install'),
			'isApplication' => (!$isApplication ? '0' : '1')
		));
		
		$this->saved();
		
		// open queue
		PackageInstallationDispatcher::openQueue(0, $processNo);
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'package' => $this->package
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::show()
	 */
	public function show() {
		if (!WCF::getSession()->getPermission('admin.system.package.canInstallPackage') && !WCF::getSession()->getPermission('admin.system.package.canUpdatePackage')) {
			throw new PermissionDeniedException();
		}
		
		// check master password
		WCFACP::checkMasterPassword();
		
		parent::show();
	}
}
