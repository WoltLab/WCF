<?php
namespace wcf\acp\page;
use wcf\page\AbstractPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;
use wcf\system\database\util\PreparedStatementConditionBuilder;

/**
 * Shows a list of purchased plugin store items.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 */
class PluginStorePurchasedItemsPage extends AbstractPage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.package';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.package.canUpdatePackage', 'admin.system.package.canUninstallPackage');
	
	/**
	 * list of purchased products grouped by WCF major release.
	 * @var	array<array>
	 */
	public $products = array();
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->products = WCF::getSession()->getVar('__pluginStoreProducts');
		if (empty($this->products)) {
			throw new IllegalLinkException();
		}
		
		die("<pre>".print_r($this->products, true));
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		$availableProducts = array();
		foreach ($this->products as $products) {
			$availableProducts = array_merge($availableProducts, array_keys($products));
		}
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("package IN (?)")
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'products' => $this->products
		));
	}
}
