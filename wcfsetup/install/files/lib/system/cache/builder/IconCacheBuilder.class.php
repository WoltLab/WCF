<?php
namespace wcf\system\cache\builder;
use wcf\data\package\Package;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\package\PackageDependencyHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Caches the paths of icons.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category 	Community Framework
 */
class IconCacheBuilder implements ICacheBuilder {
	/**
	 * @see wcf\system\cache\ICacheBuilder::getData()
	 */
	public function getData(array $cacheResource) {
		list($cache, $packageID, $styleID) = explode('-', $cacheResource['cache']); 
		$data = array();

		// get active package
		$activePackage = new Package($packageID);
		$activePackageDir = FileUtil::getRealPath(WCF_DIR.$activePackage->packageDir);
		
		// get package dirs
		$packageDirs = array();
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add("dependency.packageID IN (?) AND package.packageDir <> ''", array(PackageDependencyHandler::getInstance()->getDependencies()));
		$sql = "SELECT		DISTINCT package.packageDir, dependency.priority
			FROM		wcf".WCF_N."_package_dependency dependency
			LEFT JOIN	wcf".WCF_N."_package package
			ON		(package.packageID = dependency.dependency)
			".$conditionBuilder->__toString()."
			ORDER BY	dependency.priority DESC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
		while ($row = $statement->fetchArray()) {
			$packageDirs[] = FileUtil::getRealPath(WCF_DIR.$row['packageDir']);
		}
		$packageDirs[] = FileUtil::unifyDirSeperator(WCF_DIR);
		
		// get style icon path
		$iconDirs = array();
		$sql = "SELECT	iconPath
			FROM	wcf".WCF_N."_style
			WHERE	styleID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($styleID));
		$row = $statement->fetchArray();
		if (!empty($row['iconPath'])) $iconDirs[] = FileUtil::addTrailingSlash($row['iconPath']);
		if (!in_array('icon/', $iconDirs)) $iconDirs[] = 'icon/';
		
		// get icons
		foreach ($packageDirs as $packageDir) {
			$relativePackageDir = ($activePackageDir != $packageDir ? FileUtil::getRelativePath($activePackageDir, $packageDir) : '');
			
			foreach ($iconDirs as $iconDir) {
				$path = FileUtil::addTrailingSlash($packageDir.$iconDir);
				
				// get svg icons
				$icons = self::getIconFiles($path);
				foreach ($icons as $icon) {
					$icon = str_replace($path, '', $icon);
					if (preg_match('/^(.*)\.svg$/', $icon, $match)) {
						if (!isset($data[$match[1]])) {
							$data[$match[1]] = $relativePackageDir.$iconDir.$icon;
						}
					}
				}
			}
		}
		
		return $data;
	}
	
	/**
	 * Returns a list of SVG icons.
	 * 
	 * @param	string		$path
	 * @return	array<string>
	 */
	protected static function getIconFiles($path) {
		$files = array();
		if (is_dir($path)) {
			$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
			foreach ($iterator as $file) {
				if (preg_match('/\.svg$/', $file->getFilename())) {
					$files[] = FileUtil::unifyDirSeperator($file->getPathname());
				}
			}
		}
		
		return $files;
	}
}
