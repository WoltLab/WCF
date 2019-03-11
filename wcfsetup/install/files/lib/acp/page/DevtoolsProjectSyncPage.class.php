<?php
namespace wcf\acp\page;
use wcf\data\devtools\project\DevtoolsProject;
use wcf\page\AbstractPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Shows the devtools project sync form.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * @since       3.1
 */
class DevtoolsProjectSyncPage extends AbstractPage {
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
	 * project id
	 * @var	integer
	 */
	public $objectID = 0;
	
	/**
	 * devtools project
	 * @var DevtoolsProject
	 */
	public $object;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->objectID = intval($_REQUEST['id']);
		$this->object = new DevtoolsProject($this->objectID);
		if (!$this->object->projectID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'objectID' => $this->objectID,
			'object' => $this->object,
			'project' => $this->object
		]);
	}
}
