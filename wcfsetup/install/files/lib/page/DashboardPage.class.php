<?php
namespace wcf\page;
use wcf\system\dashboard\DashboardHandler;
use wcf\system\menu\page\PageMenu;
use wcf\system\user\collapsible\content\UserCollapsibleContentHandler;
use wcf\system\WCF;

/**
 * Shows the dashboard page.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	page
 * @category	Community Framework
 */
class DashboardPage extends AbstractPage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.user.dashboard';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededModules
	 */
	public $neededModules = array('MODULE_DASHBOARD_PAGE');
	
	/**
	 * @see	\wcf\page\AbstractPage::$enableTracking
	 */
	public $enableTracking = true;
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		// remove default breadcrumb entry
		if (PageMenu::getInstance()->getLandingPage()->menuItem == 'wcf.user.dashboard') {
			WCF::getBreadcrumbs()->remove(0);
		}
	}
	
	/**
	 * @see	\wcf\page\AbstractPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		DashboardHandler::getInstance()->loadBoxes('com.woltlab.wcf.user.DashboardPage', $this);
		
		WCF::getTPL()->assign(array(
			'sidebarCollapsed' => UserCollapsibleContentHandler::getInstance()->isCollapsed('com.woltlab.wcf.collapsibleSidebar', 'com.woltlab.wcf.user.DashboardPage'),
			'sidebarName' => 'com.woltlab.wcf.user.DashboardPage',
			'allowSpidersToIndexThisPage' => true
		));
	}
}
