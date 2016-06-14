<?php
namespace wcf\acp\page;
use wcf\page\AbstractPage;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\WCF;

/**
 * Shows the welcome page in admin control panel.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 */
class IndexPage extends AbstractPage {
	/**
	 * server information
	 * @var	string[]
	 */
	public $server = [];
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		$this->server = [
			'os' => PHP_OS,
			'webserver' => (isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : ''),
			'mySQLVersion' => WCF::getDB()->getVersion(),
			'load' => ''
		];
		
		// get load
		if (function_exists('sys_getloadavg')) {
			$load = sys_getloadavg();
			if (is_array($load) && count($load) == 3) {
				$this->server['load'] = implode(', ', $load);
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		$usersAwaitingApproval = 0;
		if (REGISTER_ACTIVATION_METHOD == 2) {
			$sql = "SELECT	COUNT(*)
				FROM	wcf".WCF_N."_user
				WHERE	activationCode <> 0";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute();
			$usersAwaitingApproval = $statement->fetchSingleColumn();
		}
		
		WCF::getTPL()->assign([
			'server' => $this->server,
			'usersAwaitingApproval' => $usersAwaitingApproval
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function show() {
		// check package installation queue
		if ($this->action == 'WCFSetup') {
			$queueID = PackageInstallationDispatcher::checkPackageInstallationQueue();
			
			if ($queueID) {
				WCF::getTPL()->assign(['queueID' => $queueID]);
				WCF::getTPL()->display('packageInstallationSetup');
				exit;
			}
		}
		
		// show page
		parent::show();
	}
}
