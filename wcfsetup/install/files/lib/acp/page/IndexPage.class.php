<?php
namespace wcf\acp\page;
use wcf\page\AbstractPage;
use wcf\system\cache\source\NoCacheSource;
use wcf\system\cache\CacheHandler;
use wcf\system\event\EventHandler;
use wcf\system\image\adapter\ImagickImageAdapter;
use wcf\system\language\LanguageFactory;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\WCF;

/**
 * Shows the welcome page in admin control panel.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 */
class IndexPage extends AbstractPage {
	/**
	 * server information
	 * @var array
	 */
	public $server = array();
	
	/**
	 * @see wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
	
		$this->server = array(
			'os' => PHP_OS,
			'webserver' => (isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : ''),		
			'mySQLVersion' => WCF::getDB()->getVersion(),
			'load' => ''
		);
		
		// get load
		if ($uptime = @exec("uptime")) {
			if (preg_match("/averages?: ([0-9\.]+,?[\s]+[0-9\.]+,?[\s]+[0-9\.]+)/", $uptime, $match)) {
				$this->server['load'] = $match[1];
			}
		}
	}
	
	/**
	 * @see wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
	
		$usersAwaitingApproval = 0;
		if (REGISTER_ACTIVATION_METHOD == 2) {
			$sql = "SELECT	COUNT(*) AS count
				FROM	wcf".WCF_N."_user
				WHERE	activationCode <> 0";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute();
			$row = $statement->fetchArray();
			$usersAwaitingApproval = $row['count'];
		}
		WCF::getTPL()->assign('usersAwaitingApproval', $usersAwaitingApproval);
		
		WCF::getTPL()->assign(array(
			'server' => $this->server
		));
	}
	
	/**
	 * @see	wcf\page\IPage::show()
	 */
	public function show() {
		// check package installation queue
		if ($this->action == 'WCFSetup') {
			$queueID = PackageInstallationDispatcher::checkPackageInstallationQueue();
			
			if ($queueID) {
				WCF::getTPL()->assign(array(
					'queueID' => $queueID
				));
				WCF::getTPL()->display('packageInstallationSetup');
				exit;
			}
		}
		
		// show page
		parent::show();
	}
}
