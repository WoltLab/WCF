<?php
namespace wcf\acp\page;
use wcf\data\devtools\project\DevtoolsProject;
use wcf\page\AbstractPage;
use wcf\system\devtools\pip\DevtoolsPip;
use wcf\system\devtools\pip\IDevtoolsPipEntryList;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the list of entries of a specific pip for a specific project.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * @since	3.2
 */
class DevtoolsProjectPipEntryListPage extends AbstractPage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.devtools.project.list';
	
	/**
	 * type of the listed pip entries
	 * @var	string
	 */
	public $entryType;
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['ENABLE_DEVELOPER_TOOLS'];
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.configuration.package.canInstallPackage'];
	
	/**
	 * name of the requested pip
	 * @var	string
	 */
	public $pip = '';
	
	/**
	 * requested pip
	 * @var	DevtoolsPip
	 */
	protected $pipObject;
	
	/**
	 * devtools project
	 * @var	DevtoolsProject
	 */
	public $project;
	
	/**
	 * project id
	 * @var	integer
	 */
	public $projectID = 0;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->projectID = intval($_REQUEST['id']);
		$this->project = new DevtoolsProject($this->projectID);
		if (!$this->project->projectID) {
			throw new IllegalLinkException();
		}
		
		$this->project->validatePackageXml();
		
		if (isset($_REQUEST['pip'])) $this->pip = StringUtil::trim($_REQUEST['pip']);
		
		$filteredPips = array_filter($this->project->getPips(), function(DevtoolsPip $pip) {
			return $pip->pluginName === $this->pip;
		});
		if (count($filteredPips) === 1) {
			$this->pipObject = reset($filteredPips);
		}
		else {
			throw new IllegalLinkException();
		}
		
		if (!$this->pipObject->supportsGui()) {
			throw new IllegalLinkException();
		}
		
		if (isset($_REQUEST['entryType'])) {
			$this->entryType = StringUtil::trim($_REQUEST['entryType']);
			
			try {
				$this->pipObject->getPip()->setEntryType($this->entryType);
			}
			catch (\InvalidArgumentException $e) {
				throw new IllegalLinkException();
			}
		}
		else if (!empty($this->pipObject->getPip()->getEntryTypes())) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		/** @var IDevtoolsPipEntryList entryList */
		$this->entryList = $this->pipObject->getPip()->getEntryList();
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'entryList' => $this->entryList,
			'entryType' => $this->entryType,
			'pip' => $this->pip,
			'project' => $this->project
		]);
	}
}
