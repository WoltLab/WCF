<?php
namespace wcf\acp\page;
use wcf\data\application\ViewableApplicationList;
use wcf\page\AbstractPage;
use wcf\system\WCF;

/**
 * Shows the application management page.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 */
class ApplicationManagementPage extends AbstractPage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.application.management';
	
	/**
	 * list of applications
	 * @var	\wcf\data\application\ViewableApplicationList
	 */
	public $applicationList = null;
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.canManageApplication');
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->applicationList = new ViewableApplicationList();
		$this->applicationList->readObjects();
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'applicationList' => $this->applicationList
		));
	}
}
