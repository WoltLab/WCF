<?php
namespace wcf\acp\form;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\data\package\installation\queue\PackageInstallationQueueEditor;
use wcf\form\AbstractForm;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\package\validation\PackageValidationException;
use wcf\system\package\validation\PackageValidationManager;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\WCF;
use wcf\system\WCFACP;
use wcf\util\FileUtil;

/**
 * Shows the package install and update form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class PackageStartInstallForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.package.install';
	
	/**
	 * updated package object
	 * @var	\wcf\data\package\Package
	 */
	public $package = null;
	
	/**
	 * data of the uploaded package
	 * @var	string[]
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
	 * location of the package uploaded via style import
	 * @var	string
	 */
	public $stylePackageImportLocation = '';
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->stylePackageImportLocation = WCF::getSession()->getVar('stylePackageImportLocation');
		if ($this->stylePackageImportLocation) {
			$_POST['t'] = WCF::getSession()->getSecurityToken();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (!$this->stylePackageImportLocation) {
			if (isset($_FILES['uploadPackage'])) $this->uploadPackage = $_FILES['uploadPackage'];
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		if ($this->stylePackageImportLocation) {
			try {
				$this->validateUploadPackage($this->stylePackageImportLocation);
			}
			catch (UserInputException $e) {
				WCF::getSession()->unregister('stylePackageImportLocation');
				
				throw $e;
			}
		}
		else if (!empty($this->uploadPackage['name'])) {
			$this->validateUploadPackage();
		}
		else {
			throw new UserInputException('uploadPackage');
		}
	}
	
	/**
	 * Validates the upload package input.
	 * 
	 * @param	string		$filename
	 * @throws	UserInputException
	 */
	protected function validateUploadPackage($filename = '') {
		$this->activeTabMenuItem = 'upload';
		
		if (empty($filename)) {
			if (empty($this->uploadPackage['tmp_name'])) {
				throw new UserInputException('uploadPackage', 'uploadFailed');
			}
			
			// get filename
			$this->uploadPackage['name'] = FileUtil::getTemporaryFilename('package_', preg_replace('!^.*(?=\.(?:tar\.gz|tgz|tar)$)!i', '', basename($this->uploadPackage['name'])));
			
			if (!@move_uploaded_file($this->uploadPackage['tmp_name'], $this->uploadPackage['name'])) {
				throw new UserInputException('uploadPackage', 'uploadFailed');
			}
			
			$filename = $this->uploadPackage['name'];
		}
		
		if (!PackageValidationManager::getInstance()->validate($filename, false)) {
			$exception = PackageValidationManager::getInstance()->getException();
			if ($exception instanceof PackageValidationException) {
				switch ($exception->getCode()) {
					case PackageValidationException::INVALID_PACKAGE_NAME:
					case PackageValidationException::MISSING_PACKAGE_XML:
						throw new UserInputException('uploadPackage', 'noValidPackage');
					break;
				}
			}
		}
		
		$this->package = PackageValidationManager::getInstance()->getPackageValidationArchive()->getPackage();
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		// get new process no
		$processNo = PackageInstallationQueue::getNewProcessNo();
		
		// obey foreign key
		$packageID = ($this->package) ? $this->package->packageID : null;
		
		$archive = null;
		if ($this->stylePackageImportLocation) {
			$archive = $this->stylePackageImportLocation;
		}
		else if (!empty($this->uploadPackage['tmp_name'])) {
			$archive = $this->uploadPackage['name'];
		}
		
		// insert queue
		$isApplication = PackageValidationManager::getInstance()->getPackageValidationArchive()->getArchive()->getPackageInfo('isApplication');
		$this->queue = PackageInstallationQueueEditor::create([
			'processNo' => $processNo,
			'userID' => WCF::getUser()->userID,
			'package' => PackageValidationManager::getInstance()->getPackageValidationArchive()->getArchive()->getPackageInfo('name'),
			'packageName' => PackageValidationManager::getInstance()->getPackageValidationArchive()->getArchive()->getLocalizedPackageInfo('packageName'),
			'packageID' => $packageID,
			'archive' => $archive,
			'action' => ($this->package != null ? 'update' : 'install'),
			'isApplication' => (!$isApplication ? '0' : '1')
		]);
		
		$this->saved();
		
		// open queue
		PackageInstallationDispatcher::openQueue(0, $processNo);
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'package' => $this->package,
			'installingImportedStyle' => $this->stylePackageImportLocation != ''
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function show() {
		if (!WCF::getSession()->getPermission('admin.configuration.package.canInstallPackage') && !WCF::getSession()->getPermission('admin.configuration.package.canUpdatePackage')) {
			throw new PermissionDeniedException();
		}
		
		// check master password
		WCFACP::checkMasterPassword();
		
		parent::show();
	}
}
