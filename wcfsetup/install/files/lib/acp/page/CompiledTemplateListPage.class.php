<?php
namespace wcf\acp\page;
use wcf\system\menu\acp\ACPMenu;
use wcf\page\AbstractPage;
use wcf\system\WCF;
use wcf\util\DirectoryUtil;

/**
 * Shows a list of all compiled tenplates.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2011 Matthias Schmidt
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class CompiledTemplateListPage extends AbstractPage {
	/**
	 * active acp menu item
	 * @todo	set value
	 * @var	string
	 */
	public $activeMenuItem = '';
	
	/**
	 * indicates if compiled templates have been deleted
	 * @var	boolean
	 */
	public $deleted = false;
	
	/**
	 * contains general cache data
	 * @var array<integer>
	 */
	public $cacheData = array(
		'size' => 0,
		'files' => 0
	);
	
	/**
	 * file information objects for the compiled template files
	 * @var	array<array>
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
	public $templateName = 'compiledTemplateList';
	
	/**
	 * @see wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (WCF::getSession()->getVar('compiledTemplatesDeleted')) {
			$this->deleted = true;
			WCF::getSession()->unregister('compiledTemplatesDeleted');
		}
	}
	
	/**
	 * @see wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->readCompiledTemplates(WCF_DIR.'acp/templates/compiled/');	
		$this->readCompiledTemplates(WCF_DIR.'templates/compiled/');	
	}
	
	/**
	 * Reads the compiled templates from the given directory.
	 * 
	 * @param	string		$directory
	 */
	protected function readCompiledTemplates($directory) {
		$this->fileInfos[$directory] = DirectoryUtil::getInstance($directory)->getFilesObj();
		foreach ($this->fileInfos[$directory] as $key => $fileInfo) {
			// filter files
			if (!$fileInfo->isFile() || !preg_match('~^(\d{1})_(\d{1})_(\d{1})_(\w+).php$~', $fileInfo->getFilename())) {
				unset($this->fileInfos[$directory][$key]);
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
			'deleted' => $this->deleted
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
