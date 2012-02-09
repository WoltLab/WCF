<?php
namespace wcf\acp\page;
use wcf\system\menu\acp\ACPMenu;
use wcf\page\AbstractPage;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\cache\source\MemcacheAdapter;
use wcf\system\cache\CacheHandler;
use wcf\system\exception\SystemException;
use wcf\system\package\PackageDependencyHandler;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\DirectoryUtil;

/**
 * Shows a list of all cache resources.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class CacheListPage extends AbstractPage {
	/**
	 * @see wcf\page\AbstractPage::$templateName
	 */
	public $templateName = 'cacheList';
	
	/**
	 * @see wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.canViewLog');
	
	/**
	 * indicates if cache was cleared
	 * @var	integer
	 */
	public $cleared = 0;
	
	/**
	 * contains a list of cache resources
	 * @var	array
	 */
	public $caches = array();
	
	/**
	 * contains general cache information
	 * @var array
	 */
	public $cacheData = array();
	
	/**
	 * @see wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['cleared'])) $this->cleared = intval($_REQUEST['cleared']);
	}
	
	/**
	 * @see wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		// init cache data
		$this->cacheData = array(
			'source' => get_class(CacheHandler::getInstance()->getCacheSource()),
			'version' => '',
			'size' => 0,
			'files' => 0
		);
		
		$_this = $this;
		$readFileCache = function($cacheDir, Regex $ignore = null) use ($_this) {
			$_this->caches[$cacheDir] = array();
			
			// get files in cache directory
			try {
				$directoryUtil = DirectoryUtil::getInstance($cacheDir);
			}
			catch(SystemException $e) {
				return;
			}
			$files = $directoryUtil->getFileObjects(SORT_ASC, new Regex('\.php$'));
			// get additional file information
			if (is_array($files)) {
				foreach ($files as $file) {
					if ($ignore !== null) if ($ignore->match($file)) continue;
					$_this->caches[$cacheDir][] = array(
						'filename' => $file->getBasename(),
						'filesize' => $file->getSize(),
						'mtime' => $file->getMtime(),
						'perm' => substr(sprintf('%o', $file->getPerms()), -3),
						'writable' => $file->isWritable()
					);
					
					$_this->cacheData['files']++;
					$_this->cacheData['size'] += $file->getSize();
				}
			}
		};
		
		// filesystem cache
		if ($this->cacheData['source'] == 'wcf\system\cache\source\DiskCacheSource') {
			// set version
			$this->cacheData['version'] = WCF_VERSION;
			
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("packageID IN (?)", array(PackageDependencyHandler::getDependencies()));
			$conditions->add("isApplication = ?", array(1));
			
			// get package dirs
			$sql = "SELECT	packageDir
				FROM	wcf".WCF_N."_package
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			while ($row = $statement->fetchArray()) {
				$packageDir = FileUtil::getRealPath(WCF_DIR.$row['packageDir']);
				$readFileCache($packageDir.'cache');
			}
		}
		// memcache
		else if ($this->cacheData['source'] == 'wcf\system\cache\source\MemcacheCacheSource') {
			// get version
			$this->cacheData['version'] = MemcacheAdapter::getInstance()->getMemcache()->getVersion();
			
			// get stats
			$stats = MemcacheAdapter::getInstance()->getMemcache()->getStats();
			$this->cacheData['files'] = $stats['curr_items'];
			$this->cacheData['size'] = $stats['bytes'];
		}
		// apc
		else if ($this->cacheData['source'] == 'wcf\system\cache\source\ApcCacheSource') {
			// set version
			$this->cacheData['version'] = phpversion('apc');
			
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("packageID IN (?)", array(PackageDependencyHandler::getDependencies()));
			$conditions->add("isApplication = ?", array(1));
			
			// get package dirs
			$sql = "SELECT	packageDir, packageName, instanceNo
				FROM	wcf".WCF_N."_package
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());

			$packageNames = array();
			while ($row = $statement->fetchArray()) {
				$packagePath = FileUtil::getRealPath(WCF_DIR.$row['packageDir']).'cache/';
				$packageNames[$packagePath] = $row['packageName'].' #'.$row['instanceNo'];
			}
			
			$apcinfo = apc_cache_info('user');
			$cacheList = $apcinfo['cache_list'];
			foreach ($cacheList as $cache) {
				$cachePath = FileUtil::addTrailingSlash(FileUtil::unifyDirSeperator(dirname($cache['info'])));
				if (isset($packageNames[$cachePath])) {
					// Use the packageName + the instance number, because pathes could confuse the administrator.
					// He could think this is a file cache. If instanceName would be unique, we could use it instead.
					$packageName = $packageNames[$cachePath];
					if (!isset($this->caches[$packageName])) $this->caches[$packageName] = array();
					
					// get additional cache information
					$this->caches[$packageName][] = array(
						'filename' => basename($cache['info'], '.php'),
						'filesize' => $cache['mem_size'],
						'mtime' => $cache['mtime'],
					);
					
					$this->cacheData['files']++;
					$this->cacheData['size'] += $cache['mem_size'];
				}
			}
		}
		// disabled
		else if ($this->cacheData['source'] == 'wcf\system\cache\source\NoCacheSource') {
			$this->cacheData['version'] = WCF_VERSION;
			$this->cacheData['files'] = $this->cacheData['size'] = 0;
		}
		
		$readFileCache(WCF_DIR.'language');
		$readFileCache(WCF_DIR.'templates/compiled', new Regex('\.meta\.php$'));
		$readFileCache(WCF_DIR.'acp/templates/compiled', new Regex('\.meta\.php$'));
	}
	
	/**
	 * @see wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'caches' => $this->caches,
			'cacheData' => $this->cacheData,
			'cleared' => $this->cleared
		));
	}
	
	/**
	 * @see wcf\page\IPage::show()
	 */
	public function show() {
		// enable menu item
		ACPMenu::getInstance()->setActiveMenuItem('wcf.acp.menu.link.log.cache');
		
		parent::show();
	}
}
