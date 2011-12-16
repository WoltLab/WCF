<?php
namespace wcf\acp\page;
use wcf\data\package\Package;
use wcf\data\package\PackageList;
use wcf\page\AbstractPage;
use wcf\system\menu\acp\ACPMenu;
use wcf\system\WCF;

/**
 * Shows a list of installed packages and plugins.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class PackageListPage extends AbstractPage {
	/**
	 * list of applications
	 * @var	wcf\data\package\PackageList
	 */
	protected $applicationList = null;
	
	/**
	 * plugin count
	 * @var	integer
	 */
	protected $pluginCount = 0;
	
	/**
	 * list of plugins
	 * @var	wcf\data\package\PackageList
	 */
	protected $pluginList = null;
	
	/**
	 * @see	wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		// read applications
		$this->applicationList = new PackageList();
		$this->applicationList->getConditionBuilder()->add("package.standalone = ?", array(1));
		$this->applicationList->getConditionBuilder()->add("package.packageID <> ?", array(1));
		$this->applicationList->sqlLimit = 0;
		$this->applicationList->readObjects();
		
		// read plugins
		$this->pluginList = Package::getPluginList();
		
		// count total plugins
		$this->pluginCount = $this->pluginList->countObjects();
		
		// read plugins
		$this->pluginList->sqlLimit = 20;
		$this->pluginList->readObjects();
	}
	
	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'applications' => $this->applicationList,
			'plugins' => $this->pluginList,
			'pluginsCount' => $this->pluginCount
		));
	}
	
	/**
	 * @see wcf\page\IPage::show()
	 */
	public function show() {
		// enable menu item
		ACPMenu::getInstance()->setActiveMenuItem('wcf.acp.menu.link.package.list');
		
		parent::show();
	}
}
