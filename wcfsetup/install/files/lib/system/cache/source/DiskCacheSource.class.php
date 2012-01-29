<?php
namespace wcf\system\cache\source;
use wcf\system\exception\SystemException;
use wcf\system\io\File;
use wcf\system\Callback;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\DirectoryUtil;

/**
 * DiskCacheSource is an implementation of CacheSource that stores the cache as simple files in the file system.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.source
 * @category 	Community Framework
 */
class DiskCacheSource implements ICacheSource {
	/**
	 * Loaded cache
	 * 
	 * @var array
	 */
	protected $cache = null;
	
	/**
	 * List of loaded resources
	 * 
	 * @var array
	 */
	protected $loaded = array();
	
	/**
	 * @see	wcf\system\cache\source\ICacheSource::get()
	 */
	public function get(array $cacheResource) {
		if (!isset($this->cache[$cacheResource['cache']])) {
			// check for rebuilt
			if ($this->needRebuild($cacheResource)) {
				return null;
			}
			
			// load resource
			if (!$this->load($cacheResource)) {
				return null;
			}
			
			if (!isset($this->cache[$cacheResource['cache']])) {
				return null;
			}
		}
		
		return $this->cache[$cacheResource['cache']];
	}
	
	/**
	 * @see	wcf\system\cache\source\ICacheSource::set()
	 */
	public function set(array $cacheResource, $value) {
		// write cache
		$targetFile = new File($cacheResource['file']);
		$targetFile->write("<?php exit; /* cache: ".$cacheResource['cache']." (generated at ".gmdate('r').") DO NOT EDIT THIS FILE */ ?>\n");
		$targetFile->write(serialize($value));
		$targetFile->close();
		
		// add value
		$this->cache[$cacheResource['cache']] = $value;
		$this->loaded[$cacheResource['file']] = true;
	}
	
	/**
	 * @see	wcf\system\cache\source\ICacheSource::delete()
	 */
	public function delete(array $cacheResource) {
		if (file_exists($cacheResource['file'])) {
			if (!@touch($cacheResource['file'], 1)) {
				@unlink($cacheResource['file']);
			}
				
			// reset open cache
			if (isset($this->cache[$cacheResource['cache']])) {
				unset($this->cache[$cacheResource['cache']]);
			}
			if (isset($this->loaded[$cacheResource['file']])) {
				unset($this->loaded[$cacheResource['file']]);	
			}
		}
	}
	
	/**
	 * @see wcf\system\cache\source\ICacheSource::clear()
	 */
	public function clear($directory, $filepattern) {
		// unify parameters
		$directory = FileUtil::unifyDirSeperator($directory);
		$filepattern = FileUtil::unifyDirSeperator($filepattern);
		
		$filepattern = str_replace('*', '.*', str_replace('.', '\.', $filepattern));
		if (substr($directory, -1) != '/') {
			$directory .= '/';	
		}

		DirectoryUtil::getInstance($directory)->executeCallback(new Callback(function ($filename) {
			if (!@touch($filename, 1)) {
				@unlink($filename);
			}
		}), new Regex('^'.$directory.$filepattern.'$', Regex::CASE_INSENSITIVE));
	}
	
	/**
	 * Determines wheater the cache needs to be rebuild or not.
	 *
	 * @param 	array 		$cacheResource
	 * @return 	boolean 	$needRebuilt
	 */
	protected function needRebuild(array $cacheResource) {
		// cache does not exist
		if (!file_exists($cacheResource['file'])) {
			return true;	
		}
		
		// cache is empty
		if (!@filesize($cacheResource['file'])) {
			return true;	
		}
		
		// cache resource was marked as obsolete
		if (($mtime = filemtime($cacheResource['file'])) <= 1) {
			return true;	
		}
		
		// maxlifetime expired
		if ($cacheResource['maxLifetime'] > 0 && (TIME_NOW - $mtime) > $cacheResource['maxLifetime']) {
			return true;	
		}
		
		// do not rebuild cache
		return false;
	}
	
	/**
	 * Loads a cached resource.
	 * 
	 * @param 	array 		$cacheResource
	 */
	public function load(array $cacheResource) {
		if (!isset($this->loaded[$cacheResource['file']])) {
			try {
				// load cache file
				$this->loadCacheFile($cacheResource);
			}
			catch (\Exception $e) {
				return false;
			}
			
			$this->loaded[$cacheResource['file']] = true;
		}
		
		return true;
	}
	
	/**
	 * Loads the file of a cached resource.
	 * 
	 * @param 	array 		$cacheResource
	 */
	protected function loadCacheFile(array $cacheResource) {
		// get file contents
		$contents = file_get_contents($cacheResource['file']);
		
		// find first newline
		$position = strpos($contents, "\n");
		if ($position === false) throw new SystemException("Unable to load cache resource '".$cacheResource['cache']."'");
		
		// cut contents
		$contents = substr($contents, $position + 1);
		
		// unserialize
		$this->cache[$cacheResource['cache']] = @unserialize($contents);
		if ($this->cache[$cacheResource['cache']] === false) throw new SystemException("Unable to load cache resource '".$cacheResource['cache']."'");
	}
	
	/**
	 * @see wcf\system\cache\source\ICacheSource::close()
	 */
	public function close() {
		// does nothing
	}
	
	/**
	 * @see wcf\system\cache\source\ICacheSource::flush()
	 */
	public function flush() {
		$sql = "SELECT		package.packageDir
			FROM		wcf".WCF_N."_package_dependency package_dependency
			LEFT JOIN	wcf".WCF_N."_package package
			ON		(package.packageID = package_dependency.dependency)
			WHERE		package_dependency.packageID = ?
					AND isApplication = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			PACKAGE_ID,
			1
		));
		while ($row = $statement->fetchArray()) {
			$packageDir = FileUtil::getRealPath(WCF_DIR.$row['packageDir']);
			$cacheDir = $packageDir.'cache';
			DirectoryUtil::getInstance($cacheDir)->removePattern(new Regex('.*\.php$'));
		}
	}
}
