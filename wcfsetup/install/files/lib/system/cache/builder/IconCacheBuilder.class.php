<?php
namespace wcf\system\cache\builder;
use wcf\system\application\ApplicationHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Caches the paths of icons.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class IconCacheBuilder implements ICacheBuilder {
	/**
	 * @see	wcf\system\cache\ICacheBuilder::getData()
	 */
	public function getData(array $cacheResource) {
		list(, $styleID) = explode('-', $cacheResource['cache']); 
		$data = array();
		
		// get style icon path
		$iconDirs = array();
		$sql = "SELECT	iconPath
			FROM	wcf".WCF_N."_style
			WHERE	styleID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($styleID));
		$row = $statement->fetchArray();
		
		if (!empty($row['iconPath'])) {
			$iconDirs[] = $row['iconPath'];
		}
		$iconDirs[] = 'icon/';
		
		if (!empty($row['iconPath'])) $iconDirs[] = FileUtil::addTrailingSlash($row['iconPath']);
		if (!in_array('icon/', $iconDirs)) $iconDirs[] = 'icon/';
		
		// get icons
		foreach ($iconDirs as $iconDir) {
			$path = FileUtil::addTrailingSlash(ApplicationHandler::getInstance()->getWCF()->getPageURL().$iconDir);
			
			// get svg icons
			$icons = self::getIconFiles($path);
			foreach ($icons as $icon) {
				$icon = str_replace($path, '', $icon);
				if (preg_match('/^(.*)\.svg$/', $icon, $match)) {
					if (!isset($data[$match[1]])) {
						$data[$match[1]] = $path.$icon;
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
