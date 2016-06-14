<?php
namespace wcf\acp\page;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\page\AbstractPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\package\validation\PackageValidationManager;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\WCF;
use wcf\system\WCFACP;

/**
 * Shows a confirmation page prior to start installing.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 */
class PackageInstallationConfirmPage extends AbstractPage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.package.install';
	
	/**
	 * package installation dispatcher object
	 * @var	\wcf\system\package\PackageInstallationDispatcher
	 */
	public $packageInstallationDispatcher = null;
	
	/**
	 * package installation queue object
	 * @var	\wcf\data\package\installation\queue\PackageInstallationQueue
	 */
	public $queue = null;
	
	/**
	 * queue id
	 * @var	integer
	 */
	public $queueID = 0;
	
	/**
	 * package validation result
	 * @var	boolean
	 */
	public $validationPassed = false;
	
	/**
	 * true if the package to be installed was uploaded via the import style
	 * form
	 * @var	boolean
	 */
	public $installingImportedStyle = false;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['queueID'])) $this->queueID = intval($_REQUEST['queueID']);
		$this->queue = new PackageInstallationQueue($this->queueID);
		if (!$this->queue->queueID || $this->queue->done) {
			throw new IllegalLinkException();
		}
		
		if ($this->queue->action == 'install') {
			WCF::getSession()->checkPermissions(['admin.configuration.package.canInstallPackage']);
		}
		else {
			WCF::getSession()->checkPermissions(['admin.configuration.package.canUpdatePackage']);
		}
		
		$this->installingImportedStyle = WCF::getSession()->getVar('stylePackageImportLocation') !== null;
		if ($this->installingImportedStyle) {
			WCF::getSession()->unregister('stylePackageImportLocation');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		$this->packageInstallationDispatcher = new PackageInstallationDispatcher($this->queue);
		
		// validate the package and all it's requirements
		$this->validationPassed = PackageValidationManager::getInstance()->validate($this->queue->archive, true);
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'archive' => $this->packageInstallationDispatcher->getArchive(),
			'packageValidationArchives' => PackageValidationManager::getInstance()->getPackageValidationArchiveList(),
			'queue' => $this->queue,
			'validationPassed' => $this->validationPassed,
			'installingImportedStyle' => $this->installingImportedStyle
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function show() {
		// check master password
		WCFACP::checkMasterPassword();
		
		parent::show();
	}
}
