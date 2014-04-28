<?php
namespace wcf\acp\page;
use wcf\page\AbstractPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\data\package\PackageCache;
use wcf\data\package\Package;
use wcf\data\package\update\server\PackageUpdateServerList;

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
	
	public $productData = array();
	
	public $updateServers = array();
	
	public $wcfMajorReleases = array();
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->products = WCF::getSession()->getVar('__pluginStoreProducts');
		if (empty($this->products)) {
			throw new IllegalLinkException();
		}
		
		$this->wcfMajorReleases = WCF::getSession()->getVar('__pluginStoreWcfMajorReleases');
		if (empty($this->wcfMajorReleases)) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		$serverList = new PackageUpdateServerList();
		$serverList->readObjects();
		foreach ($serverList as $server) {
			if (preg_match('~https?://store.woltlab.com/(?P<wcfMajorRelease>[a-z]+)/~', $server->serverURL, $matches)) {
				$this->updateServers[$matches['wcfMajorRelease']] = $server;
			}
		}
		
		foreach ($this->products as $packageUpdateID => $product) {
			$wcfMajorRelease = $product['wcfMajorRelease'];
			if (!isset($this->productData[$wcfMajorRelease])) {
				$this->productData[$wcfMajorRelease] = array();
			}
			
			$languageCode = WCF::getLanguage()->languageCode;
			$packageName = (isset($product['packageName'][$languageCode])) ? $product['packageName'][$languageCode] : $product['packageName']['en'];
			
			$this->productData[$wcfMajorRelease][$packageUpdateID] = array(
				'author' => $product['author'],
				'authorURL' => $product['authorURL'],
				'package'  => $product['package'],
				'packageName' => $packageName,
				'pluginStoreURL' => $product['pluginStoreURL'],
				'version' => array(
					'available' => $product['lastVersion'],
					'installed' => ''
				),
				'status' => (isset($this->updateServers[$wcfMajorRelease]) ? 'install' : 'unavailable')
			);
			
			$package = PackageCache::getInstance()->getPackageByIdentifier($product['package']);
			if ($package !== null) {
				$this->productData[$wcfMajorRelease][$packageUpdateID]['version']['installed'] = $package->packageVersion;
				
				if (Package::compareVersion($product['lastVersion'], $package->packageVersion, '>')) {
					$this->productData[$wcfMajorRelease][$packageUpdateID]['status'] = 'update';
				}
			}
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'productData' => $this->productData,
			'updateServers' => $this->updateServers,
			'wcfMajorReleases' => $this->wcfMajorReleases
		));
	}
}
