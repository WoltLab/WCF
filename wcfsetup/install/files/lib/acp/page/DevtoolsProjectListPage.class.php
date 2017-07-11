<?php
namespace wcf\acp\page;
use wcf\data\devtools\project\DevtoolsProjectList;
use wcf\page\AbstractPage;
use wcf\system\WCF;

/**
 * Shows a list of devtools projects.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * @since	3.1
 */
class DevtoolsProjectListPage extends AbstractPage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.devtools.project.list';
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['ENABLE_DEVELOPER_TOOLS'];
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.configuration.package.canInstallPackage'];
	
	/**
	 * @var DevtoolsProjectList
	 */
	public $objectList;
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		$this->objectList = new DevtoolsProjectList();
		$this->objectList->readObjects();
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'objects' => $this->objectList
		]);
	}
}
