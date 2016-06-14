<?php
namespace wcf\data\page;
use wcf\data\package\PackageCache;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\PageCacheBuilder;
use wcf\system\cache\builder\RoutingCacheBuilder;
use wcf\system\request\ControllerMap;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Provides functions to edit pages.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Page
 * @since	3.0
 * 
 * @method	Page	getDecoratedObject()
 * @mixin	Page
 */
class PageEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Page::class;
	
	/**
	 * @inheritDoc
	 */
	public static function resetCache() {
		RoutingCacheBuilder::getInstance()->reset();
		PageCacheBuilder::getInstance()->reset();
	}
	
	/**
	 * Returns true if given custom url is unique.
	 * 
	 * @param       string          $customURL
	 * @param       integer         $packageID
	 *
	 * @return      boolean
	 */
	public static function isUniqueCustomUrl($customURL, $packageID = 1) {
		// check controller
		$package = PackageCache::getInstance()->getPackage($packageID);
		$packageDir = FileUtil::addTrailingSlash(FileUtil::getRealPath(WCF_DIR.$package->packageDir));
		
		$files = array_merge(glob($packageDir . 'lib/action/*.php'), glob($packageDir . 'lib/form/*.php'), glob($packageDir . 'lib/page/*.php'));
		foreach ($files as $file) {
			$filename = preg_replace('/(Action|Page|Form)(\.class)?\.php$/', '', basename($file));
			if ($customURL == ControllerMap::transformController($filename)) {
				return false;
			}
		}
		
		// check custom controller urls
		$sql = "SELECT  COUNT(*) AS count
			FROM    wcf".WCF_N."_page
			WHERE   controllerCustomURL = ?
				AND applicationPackageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$customURL, $packageID]);
		if ($statement->fetchColumn()) {
			return false;
		}
		
		// check custom urls
		$sql = "SELECT  COUNT(*) AS count
			FROM    wcf".WCF_N."_page_content
			WHERE   customURL = ?
				AND pageID IN (
					SELECT  pageID
					FROM    wcf".WCF_N."_page
					WHERE   applicationPackageID = ?
				)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$customURL, $packageID]);
		if ($statement->fetchColumn()) {
			return false;
		}
		
		return true;
	}
}
