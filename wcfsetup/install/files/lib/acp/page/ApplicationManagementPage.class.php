<?php
namespace wcf\acp\page;
use wcf\data\application\ViewableApplicationList;
use wcf\data\page\PageList;
use wcf\page\AbstractPage;
use wcf\system\WCF;

/**
 * Shows the application management page.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 */
class ApplicationManagementPage extends AbstractPage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.application.management';
	
	/**
	 * list of applications
	 * @var	ViewableApplicationList
	 */
	public $applicationList = null;
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.configuration.canManageApplication'];
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		$this->applicationList = new ViewableApplicationList();
		$this->applicationList->readObjects();
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		$pageList = new PageList();
		$pageList->readObjects();
		
		WCF::getTPL()->assign([
			'applicationList' => $this->applicationList,
			'pageList' => $pageList->getObjects()
		]);
	}
}
