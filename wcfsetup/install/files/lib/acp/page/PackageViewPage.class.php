<?php
namespace wcf\acp\page;
use wcf\data\package\Package;
use wcf\page\AbstractPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\menu\acp\ACPMenu;
use wcf\system\WCF;

/**
 * Shows all information about an installed package.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 */
class PackageViewPage extends AbstractPage {
	/**
	 * @see	wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.package.canUpdatePackage', 'admin.system.package.canUninstallPackage');
	
	/**
	 * id of the package
	 * @var	integer
	 */
	public $packageID = 0;
	
	/**
	 * package object
	 * @var	wcf\data\package\Package
	 */
	public $package = null;
	
	/**
	 * @see	wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->packageID = intval($_REQUEST['id']);
		$this->package = new Package($this->packageID);
		if (!$this->package->packageID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign('package', $this->package);
	}
	
	/**
	 * @see	wcf\page\IPage::show()
	 */
	public function show() {
		// enable menu item
		ACPMenu::getInstance()->setActiveMenuItem('wcf.acp.menu.link.package');
		
		parent::show();
	}
}
