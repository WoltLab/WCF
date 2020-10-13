<?php
namespace wcf\acp\page;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\data\package\update\server\PackageUpdateServerList;
use wcf\data\package\Package;
use wcf\data\package\PackageCache;
use wcf\page\AbstractPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Shows a list of purchased plugin store items.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 */
class PluginStorePurchasedItemsPage extends AbstractPage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.package';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.configuration.package.canUpdatePackage', 'admin.configuration.package.canUninstallPackage'];
	
	public $fetchedPackageServers = false;
	
	/**
	 * list of purchased products grouped by WCF major release
	 * @var	array<array>
	 */
	public $products = [];
	
	/**
	 * list of product data grouped by WCF major release
	 * @var	array<array>
	 */
	public $productData = [];
	
	/**
	 * list of installed update servers (Plugin-Store only)
	 * @var	PackageUpdateServer[]
	 */
	public $updateServers = [];
	
	/**
	 * list of supported WCF major releases (Plugin-Store)
	 * @var	string[]
	 */
	public $wcfMajorReleases = [];
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		foreach (PackageUpdateServer::getActiveUpdateServers() as $packageUpdateServer) {
			if ($packageUpdateServer->isWoltLabUpdateServer() || $packageUpdateServer->isWoltLabStoreServer()) {
				$this->fetchedPackageServers = $packageUpdateServer->lastUpdateTime > 0;
				break;
			}
		}
		
		foreach ($this->products as $packageUpdateID => $product) {
			$languageCode = WCF::getLanguage()->languageCode;
			$packageName = isset($product['packageName'][$languageCode]) ? $product['packageName'][$languageCode] : $product['packageName']['en'];
			
			$this->productData[$packageUpdateID] = [
				'author' => $product['author'],
				'authorURL' => $product['authorURL'],
				'package' => $product['package'],
				'packageName' => $packageName,
				'pluginStoreURL' => $product['pluginStoreURL'],
				'version' => [
					'available' => $product['lastVersion'],
					'installed' => ''
				],
				'status' => 'install',
			];
			
			$package = PackageCache::getInstance()->getPackageByIdentifier($product['package']);
			if ($package !== null) {
				$this->productData[$packageUpdateID]['version']['installed'] = $package->packageVersion;
				
				if (Package::compareVersion($product['lastVersion'], $package->packageVersion, '>')) {
					$this->productData[$packageUpdateID]['status'] = 'update';
				}
				else if (Package::compareVersion($product['lastVersion'], $package->packageVersion, '<=')) {
					$this->productData[$packageUpdateID]['status'] = 'upToDate';
				}
			}
			
			if (!$this->fetchedPackageServers) {
				$this->productData[$packageUpdateID]['status'] = 'requireUpdate';
			}
		}
		
		uasort($this->productData, function (array $a, array $b) {
			return strcasecmp($a['packageName'], $b['packageName']);
		});
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'fetchedPackageServers' => $this->fetchedPackageServers,
			'productData' => $this->productData,
		]);
	}
}
