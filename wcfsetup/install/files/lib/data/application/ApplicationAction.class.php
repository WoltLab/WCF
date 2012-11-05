<?php
namespace wcf\data\application;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\package\PackageCache;
use wcf\system\cache\CacheHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Executes application-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.application
 * @category	Community Framework
 */
class ApplicationAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\application\ApplicationEditor';
	
	/**
	 * Assigns a list of applications to a group and computes cookie domain and path.
	 */
	public function group() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		$sql = "UPDATE	wcf".WCF_N."_application
			SET	groupID = ?,
				cookieDomain = ?,
				cookiePath = ?
			WHERE	packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		// calculate cookie path
		$domains = array();
		foreach ($this->objects as $application) {
			if (!isset($domains[$application->domainName])) {
				$domains[$application->domainName] = array();
			}
			
			$domains[$application->domainName][$application->packageID] = explode('/', FileUtil::removeLeadingSlash(FileUtil::removeTrailingSlash($application->domainPath)));
		}
		
		WCF::getDB()->beginTransaction();
		foreach ($domains as $domainName => $data) {
			$path = null;
			foreach ($data as $domainPath) {
				if ($path === null) {
					$path = $domainPath;
				}
				else {
					foreach ($path as $i => $part) {
						if (!isset($domainPath[$i]) || $domainPath[$i] != $part) {
							// remove all following elements including current one
							foreach ($path as $j => $innerPart) {
								if ($j >= $i) {
									unset($path[$j]);
								}
							}
							
							// skip to next domain
							continue 2;
						}
					}
				}
			}
			
			$path = FileUtil::addLeadingSlash(FileUtil::addTrailingSlash(implode('/', $path)));
			
			foreach (array_keys($data) as $packageID) {
				$statement->execute(array(
					$this->parameters['groupID'],
					$domainName,
					$path,
					$packageID
				));
			}
		}
		WCF::getDB()->commitTransaction();
		
		$this->clearCache();
	}
	
	/**
	 * Removes a list of applications from their group and resets the cookie domain and path.
	 */
	public function ungroup() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		$this->clearCache();
		$sql = "UPDATE	wcf".WCF_N."_application
			SET	groupID = ?,
				cookieDomain = domainName,
				cookiePath = domainPath
			WHERE	packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		WCF::getDB()->beginTransaction();
		foreach ($this->objects as $application) {
			$statement->execute(array(
				null,
				$application->packageID
			));
		}
		WCF::getDB()->commitTransaction();
		
		$this->clearCache();
	}
	
	/**
	 * Clears application cache.
	 */
	protected function clearCache() {
		foreach ($this->objects as $application) {
			$directory = PackageCache::getInstance()->getPackage($application->packageID)->packageDir;
			$directory = FileUtil::getRealPath(WCF_DIR.$directory);
			
			CacheHandler::getInstance()->clear($directory.'cache', '*.php');
		}
	}
}
