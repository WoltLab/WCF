<?php
namespace wcf\system\cache\builder;
use wcf\data\package\Package;
use wcf\system\cache\CacheBuilder;
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
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class CacheBuilderIcon implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		list($cache, $packageID, $styleID) = explode('-', $cacheResource['cache']); 
		$data = array();

		// get active package
		$activePackage = new Package($packageID);
		$activePackageDir = FileUtil::getRealPath(WCF_DIR.$activePackage->getDir());
		
		// get package dirs
		$packageDirs = array();
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add("packageID IN (?) AND packageDir <> ''", array(PackageDependencyHandler::getDependenciesString()));
		$sql = "SELECT		DISTINCT packageDir
			FROM		wcf".WCF_N."_package package
			".$conditionBuilder->__toString()."
			ORDER BY	priority DESC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
		while ($row = $statement->fetchArray()) {
			$packageDirs[] = FileUtil::getRealPath(WCF_DIR.$row['packageDir']);
		}
		$packageDirs[] = WCF_DIR;
		
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
			
			foreach ($iconDirs as $iconDir) {
				$path = FileUtil::addTrailingSlash($packageDir.$iconDir);
				$icons = self::getIconFiles($path);
				foreach ($icons as $icon) {
					$icon = str_replace($path, '', $icon);
					if (!isset($data[$icon])) {
						$data[$icon] = $relativePackageDir.$iconDir.$icon;
					}
				}
			}
		}
		
		return $data;
	}
	
	protected static function getIconFiles($path) {
		$files = array();
		if (is_dir($path)) {
			$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
			foreach ($iterator as $file) {
				if (preg_match('/\.png$/', $file->getFilename())) {
					$files[] = $file->getPathname();
				}
			}
		}
		
		return $files;
	}
}
