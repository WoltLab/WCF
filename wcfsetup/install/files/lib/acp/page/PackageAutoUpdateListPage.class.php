<?php
namespace wcf\acp\page;
use wcf\page\AbstractPage;
use wcf\system\menu\acp\ACPMenu;
use wcf\system\package\PackageUpdateDispatcher;
use wcf\system\WCF;
use wcf\system\WCFACP;

/**
 * Shows the list of available updates for installed packages.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class PackageAutoUpdateListPage extends AbstractPage {
	/**
	 * @see wcf\page\AbstractPage::$templateName
	 */
	public $templateName = 'packageAutoUpdateList';

	/**
	 * @see wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.package.canUpdatePackage');
	
	/**
	 * list with data of available updates
	 * @var	array
	 */
	public $availableUpdates = array();
	
	/**
	 * @see wcf\page\IPage::assignVariables()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!count($_POST)) {
			// refresh package database
			PackageUpdateDispatcher::refreshPackageDatabase();
		}
		
		// get updatable packages
		$this->availableUpdates = PackageUpdateDispatcher::getAvailableUpdates();
	}
	
	/**
	 * @see wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'availableUpdates' => $this->availableUpdates
		));
	}
	
	/**
	 * @see wcf\page\IPage::show()
	 */
	public function show() {
		// set active menu item
		ACPMenu::getInstance()->setActiveMenuItem('wcf.acp.menu.link.package.autoupdate');
		
		// check master password
		WCFACP::checkMasterPassword();
		
		parent::show();
	}
}
