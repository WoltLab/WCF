<?php
namespace wcf\acp\action;
use wcf\action\AbstractDialogAction;
use wcf\data\devtools\project\DevtoolsProject;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\system\devtools\pip\DevtoolsPackageInstallationDispatcher;
use wcf\system\exception\IllegalLinkException;
use wcf\system\request\LinkHandler;
use wcf\util\StringUtil;

/**
 * Handles an AJAX-based package installation of devtools projects.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Action
 * @since	5.2
 */
class DevtoolsInstallPackageAction extends InstallPackageAction {
	/**
	 * project whose source is installed as a package
	 * @var	DevtoolsProject
	 */
	public $project;
	
	/**
	 * id of the project whose source is installed as a package
	 * @var	int
	 */
	public $projectID;
	
	/**
	 * @inheritDoc
	 */
	protected function getRedirectLink() {
		return LinkHandler::getInstance()->getLink('DevtoolsProjectList');
	}
	
	/**
	 * @inheritDoc
	 * @throws	IllegalLinkException
	 */
	public function readParameters() {
		AbstractDialogAction::readParameters();
		
		if (isset($_POST['projectID'])) $this->projectID = intval($_POST['projectID']);
		$this->project = new DevtoolsProject($this->projectID);
		if (!$this->project->projectID) {
			throw new IllegalLinkException();
		}
		
		if (isset($_POST['node'])) $this->node = StringUtil::trim($_POST['node']);
		
		if (isset($_POST['queueID'])) $this->queueID = intval($_POST['queueID']);
		$this->queue = new PackageInstallationQueue($this->queueID);
		if (!$this->queue->queueID) {
			throw new IllegalLinkException();
		}
		
		$this->installation = new DevtoolsPackageInstallationDispatcher($this->project, $this->queue);
	}
}
