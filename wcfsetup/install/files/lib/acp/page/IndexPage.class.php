<?php
namespace wcf\acp\page;
use wcf\page\AbstractPage;
use wcf\system\cache\CacheHandler;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\WCF;
use wcf\system\WCFACP;

/**
 * Shows the welcome page in admin control panel.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class IndexPage extends AbstractPage {
	// system
	public $templateName = 'index';
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		$wcfPackageID = WCFACP::getWcfPackageID();
		// check package installation queue
		if ($wcfPackageID == 0) {
			$queueID = PackageInstallationDispatcher::checkPackageInstallationQueue();
			
			if ($queueID) {
				WCF::getTPL()->assign(array(
					'queueID' => $queueID
				));
				WCF::getTPL()->display('packageInstallationSetup');
				exit;
			}
		}
		
		/*
		if (WCFACP::getWcfPackageID() == PACKAGE_ID) {
			$packages = CacheHandler::getInstance()->get('packages');
			foreach ($packages as $packageID => $package) {
				break;
			}
			
			if (isset($packageID) && $packageID != PACKAGE_ID) {
				HeaderUtil::redirect('../'.$packages[$packageID]['packageDir'].'acp/index.php'.SID_ARG_1ST, false);
				exit;
			}
		}
		*/
		
		// show page
		parent::show();
	}
}
?>