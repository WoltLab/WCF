<?php
namespace wcf\acp\page;
use wcf\system\menu\acp\ACPMenu;
use wcf\page\AbstractPage;
use wcf\system\WCF;
use wcf\system\WCFACP;
use wcf\system\package\PackageUpdateDispatcher;

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
	public $templateName = 'packageAutoUpdateList';
	public $neededPermissions = array('admin.system.package.canUpdatePackage');
	
	public $availableUpdates = array();
	
	/**
	 * @see	Page::assignVariables()
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
	 * @see	Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'availableUpdates' => $this->availableUpdates
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// set active menu item
		ACPMenu::getInstance()->setActiveMenuItem('wcf.acp.menu.link.autoupdate');
		
		// check master password
		WCFACP::checkMasterPassword();
		
		parent::show();
	}
}
