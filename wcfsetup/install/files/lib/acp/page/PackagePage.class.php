<?php
namespace wcf\acp\page;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\page\AbstractPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\package\PackageUninstallationDispatcher;
use wcf\system\WCF;
use wcf\system\WCFACP;

/**
 * Handles all request on the package.php script
 * and executes the requested action.
 * TODO: split this page into separate pages / actions
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class PackagePage extends AbstractPage {
	const DO_NOT_LOG = true;
	public $parentQueueID = 0;
	public $processNo = 0;
	public $queueID = 0;
	
	/**
	 * @see wcf\page\Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['parentQueueID'])) $this->parentQueueID = intval($_REQUEST['parentQueueID']);
		if (isset($_REQUEST['processNo'])) $this->processNo = intval($_REQUEST['processNo']);
		if (isset($_REQUEST['queueID'])) $this->queueID = intval($_REQUEST['queueID']);
	}
	
	/**
	 * @see wcf\page\Page::show()
	 */
	public function show() {
		parent::show();

		// check master password
		WCFACP::checkMasterPassword();
		
		switch ($this->action) {
			case 'install':
			case 'update':
				if ($this->action == 'install') WCF::getSession()->checkPermission(array('admin.system.package.canInstallPackage'));
				else WCF::getSession()->checkPermission(array('admin.system.package.canUpdatePackage'));
				
				$queue = new PackageInstallationQueue($this->queueID);
				$dispatcher = new PackageInstallationDispatcher($queue);
				$dispatcher->beginInstallation();
			break;
				
			case 'rollback':
				die('ROLLBACK');
				WCF::getSession()->checkPermission(array('admin.system.package.canInstallPackage'));
				require_once(WCF_DIR.'lib/acp/package/PackageInstallationRollback.class.php');
				new PackageInstallationRollback($this->queueID);
			break;
			
			case 'openQueue':
				PackageInstallationDispatcher::openQueue($this->parentQueueID, $this->processNo);
			break;
				
			case 'startUninstall':
				WCF::getSession()->checkPermission(array('admin.system.package.canUninstallPackage'));
				PackageUninstallationDispatcher::checkDependencies();
			break;
		}
	}
}
