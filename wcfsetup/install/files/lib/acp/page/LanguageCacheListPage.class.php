<?php
namespace wcf\acp\page;
use wcf\system\menu\acp\ACPMenu;
use wcf\page\AbstractPage;
use wcf\system\WCF;
use wcf\util\DirectoryUtil;

/**
 * Shows a list of all language cache resources.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2011 Matthias Schmidt
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class LanguageCacheListPage extends AbstractPage {
	/**
	 * active acp menu item
	 * @var	string
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.cache.language';
	
	/**
	 * indicates if language cache has just been cleared
	 * @var	boolean
	 */
	public $cleared = false;
	
	/**
	 * contains general cache data
	 * @var array<integer>
	 */
	public $cacheData = array();
	
	/**
	 * file information objects for the langage cache files
	 * @var	array<\SplFileInfo>
	 */
	public $fileInfos = array();
	
	/**
	 * @see wcf\page\AbstractPage::$neededPermissions
	 * @todo	use own permission 'admin.system.canClearCache' for all cache types(?)
	 */
	public $neededPermissions = array('admin.system.canViewLog');
	
	/**
	 * @see wcf\page\AbstractPage::$templateName
	 */
	public $templateName = 'languageCacheList';
	
	/**
	 * @see wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (WCF::getSession()->getVar('languageCacheCleared')) {
			$this->cleared = true;
			WCF::getSession()->unregister('languageCacheCleared');
		}
	}
	
	/**
	 * @see wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		// init cache data
		$this->cacheData = array(
			'size' => 0,
			'files' => 0
		);
		
		$this->fileInfos = DirectoryUtil::getInstance(WCF_DIR.'language/')->getFilesObj();
		foreach ($this->fileInfos as $key => $fileInfo) {
			// filter files
			if (!$fileInfo->isFile() || !preg_match('~^(\d{1})_(\d{1})_(.+).php$~', $fileInfo->getFilename())) {
				unset($this->fileInfos[$key]);
				continue;
			}
			
			$this->cacheData['files']++;
			$this->cacheData['size'] += $fileInfo->getSize();
		}
	}
	
	/**
	 * @see wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'fileInfos' => $this->fileInfos,
			'cacheData' => $this->cacheData,
			'cleared' => $this->cleared
		));
	}
	
	/**
	 * @see wcf\page\IPage::show()
	 */
	public function show() {
		// set active acp menu item
		ACPMenu::getInstance()->setActiveMenuItem($this->activeMenuItem);
		
		parent::show();
	}
}
