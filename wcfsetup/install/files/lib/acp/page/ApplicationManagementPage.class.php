<?php
namespace wcf\acp\page;
use wcf\data\application\ApplicationList;
use wcf\data\application\group\ApplicationGroupList;
use wcf\data\application\group\ViewableApplicationGroup;
use wcf\page\AbstractPage;
use wcf\system\menu\acp\ACPMenu;
use wcf\system\WCF;

/**
 * Shows the application management page.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class ApplicationManagementPage extends AbstractPage {
	/**
	 * list of ungrouped applications
	 * @var	array<wcf\data\application\Application>
	 */
	public $applications = null;
	
	/**
	 * list of viewable application groups
	 * @var	array<wcf\data\application\group\ViewableApplicationGroup>
	 */
	public $applicationGroups = null;
	
	/**
	 * number of ungrouped applications
	 * @var	integer
	 */
	public $ungroupedApplications = 0;
	
	/**
	 * @see	wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		$applicationList = new ApplicationList();
		$applicationList->sqlSelects = "package.packageName";
		$applicationList->sqlJoins = "LEFT JOIN wcf".WCF_N."_package package ON (package.packageID = application.packageID)";
		$applicationList->getConditionBuilder()->add("application.packageID <> ?", array(1)); // exclude WCF pseudo-application
		$applicationList->sqlLimit = 0;
		$applicationList->readObjects();
		
		$applicationGroupList = new ApplicationGroupList();
		$applicationGroupList->sqlLimit = 0;
		$applicationGroupList->readObjects();
		foreach ($applicationGroupList as $applicationGroup) {
			$this->applicationGroups[$applicationGroup->groupID] = new ViewableApplicationGroup($applicationGroup);
		}
		
		foreach ($applicationList as $application) {
			if (!$application->groupID) {
				$this->applications[$application->packageID] = $application;
			}
			else {
				$this->applicationGroups[$application->groupID]->addApplication($application);
			}
		}
	}
	
	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'applications' => $this->applications,
			'applicationGroups' => $this->applicationGroups
		));
	}
	
	/**
	 * @see	wcf\page\IPage::show()
	 */
	public function show() {
		// enable menu item
		ACPMenu::getInstance()->setActiveMenuItem('wcf.acp.menu.link.application.management');
	
		parent::show();
	}
}
