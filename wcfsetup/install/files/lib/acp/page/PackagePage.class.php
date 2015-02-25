<?php
namespace wcf\acp\page;
use wcf\data\package\Package;
use wcf\page\AbstractPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Shows all information about an installed package.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 */
class PackagePage extends AbstractPage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.package';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.package.canUpdatePackage', 'admin.system.package.canUninstallPackage');
	
	/**
	 * id of the package
	 * @var	integer
	 */
	public $packageID = 0;
	
	/**
	 * package object
	 * @var	\wcf\data\package\Package
	 */
	public $package = null;
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
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
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign('package', $this->package);
	}
}
