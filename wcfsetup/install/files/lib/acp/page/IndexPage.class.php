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
