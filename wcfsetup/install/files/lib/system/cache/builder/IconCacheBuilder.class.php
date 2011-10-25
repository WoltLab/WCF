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
		$conditionBuilder->add("dependency.packageID IN (?) AND package.packageDir <> ''", array(PackageDependencyHandler::getDependencies()));
		$sql = "SELECT		DISTINCT package.packageDir
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
		$sql = "SELECT	variableValue
			FROM	wcf".WCF_N."_style_variable
			WHERE	styleID = ?
				AND variableName = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($styleID, 'global.icons.location'));
		$row = $statement->fetchArray();
		if (!empty($row['variableValue'])) $iconDirs[] = FileUtil::addTrailingSlash($row['variableValue']);
		if (!in_array('icon/', $iconDirs)) $iconDirs[] = 'icon/';
		
		// get icons
		foreach ($packageDirs as $packageDir) {
			$relativePackageDir = ($activePackageDir != $packageDir ? FileUtil::getRelativePath($activePackageDir, $packageDir) : '');
			echo $relativePackageDir."\n";
			foreach ($iconDirs as $iconDir) {
				$path = FileUtil::addTrailingSlash($packageDir.$iconDir);
				
				// get png icons
				$icons = self::getIconFiles($path, 'png');
				foreach ($icons as $icon) {
					$icon = str_replace($path, '', $icon);
					if (preg_match('/^(.*)(S|M|L)\.png$/', $icon, $match)) {
						if (!isset($data[$match[1]][$match[2]])) {
							$data[$match[1]][$match[2]] = $relativePackageDir.$iconDir.$icon;
						}
					}
				}
				
				// get svg icons
				$icons = self::getIconFiles($path, 'svg');
				foreach ($icons as $icon) {
					$icon = str_replace($path, '', $icon);
					if (preg_match('/^(.*)\.svg$/', $icon, $match)) {
						if (!isset($data[$match[1]]['S'])) {
							$data[$match[1]]['S'] = $relativePackageDir.$iconDir.$icon;
						}
						if (!isset($data[$match[1]]['M'])) {
							$data[$match[1]]['M'] = $relativePackageDir.$iconDir.$icon;
						}
						if (!isset($data[$match[1]]['L'])) {
							$data[$match[1]]['L'] = $relativePackageDir.$iconDir.$icon;
						}
					}
				}
			}
		}
		
		return $data;
	}
	
	protected static function getIconFiles($path, $extension = 'svg') {
		$files = array();
		if (is_dir($path)) {
			$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
			foreach ($iterator as $file) {
				if (preg_match('/\.'.$extension.'$/', $file->getFilename())) {
					$files[] = FileUtil::unifyDirSeperator($file->getPathname());
				}
			}
		}
		
		return $files;
	}
}
