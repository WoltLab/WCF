<?php
namespace wcf\acp\page;
use wcf\data\devtools\project\DevtoolsProject;
use wcf\page\AbstractPage;
use wcf\system\devtools\pip\DevtoolsPip;
use wcf\system\devtools\pip\IDevtoolsPipEntryList;
use wcf\system\exception\IllegalLinkException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the list of entries of a specific pip for a specific project.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * @since	5.2
 */
class DevtoolsProjectPipEntryListPage extends AbstractPage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.devtools.project.list';
	
	/**
	 * indicates the range of the listed items
	 * @var	integer
	 */
	public $endIndex = 0;
	
	/**
	 * entry filter string
	 * @var	string
	 */
	public $entryFilter;
	
	/**
	 * pip entry list
	 * @var	IDevtoolsPipEntryList
	 */
	public $entryList;
	
	/**
	 * type of the listed pip entries
	 * @var	string
	 */
	public $entryType;
	
	/**
	 * @inheritDoc
	 */
	public $forceCanonicalURL = true;
	
	/**
	 * number of items shown per page
	 * @var	integer
	 */
	public $itemsPerPage = 100;
	
	/**
	 * number of all items
	 * @var	integer
	 */
	public $items = 0;
	
	/**
	 * pagination link parameters
	 * @var	string
	 */
	public $linkParameters = '';
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['ENABLE_DEVELOPER_TOOLS'];
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.configuration.package.canInstallPackage'];
	
	/**
	 * current page number
	 * @var	integer
	 */
	public $pageNo = 0;
	
	/**
	 * number of all pages
	 * @var	integer
	 */
	public $pages = 0;
	
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
	 * indicates the range of the listed items
	 * @var	integer
	 */
	public $startIndex = 0;
	
	/**
	 * @inheritDoc
	 * @throws	IllegalLinkException
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->projectID = intval($_REQUEST['id']);
		$this->project = new DevtoolsProject($this->projectID);
		if (!$this->project->projectID) {
			throw new IllegalLinkException();
		}
		
		if ($this->project->validatePackageXml() !== '') {
			throw new IllegalLinkException();
		}
		
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
		
		if (isset($_REQUEST['pageNo'])) $this->pageNo = intval($_REQUEST['pageNo']);
		
		$this->linkParameters = 'pip=' . $this->pip;
		if ($this->entryType !== null) {
			$this->linkParameters .= '&entryType=' . $this->entryType;
		}
		
		if (isset($_REQUEST['entryFilter'])) $this->entryFilter = StringUtil::trim($_REQUEST['entryFilter']);
		
		if ($this->entryFilter !== null && $this->entryFilter !== '') {
			$this->linkParameters .= '&entryFilter=' . $this->entryFilter;
		}
		
		$this->canonicalURL = LinkHandler::getInstance()->getLink('DevtoolsProjectPipEntryList', [
			'id' => $this->project->projectID,
		], $this->linkParameters);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		/** @var IDevtoolsPipEntryList entryList */
		$this->entryList = $this->pipObject->getPip()->getEntryList();
		
		if ($this->entryFilter !== null && $this->entryFilter !== '') {
			$this->entryList->filterEntries($this->entryFilter);
		}
		
		$this->items = count($this->entryList->getEntries());
		$this->pages = intval(ceil($this->items / $this->itemsPerPage));
		
		// correct active page number
		if ($this->pageNo > $this->pages) $this->pageNo = $this->pages;
		if ($this->pageNo < 1) $this->pageNo = 1;
		
		// calculate start and end index
		$this->startIndex = ($this->pageNo - 1) * $this->itemsPerPage;
		$this->endIndex = $this->startIndex + $this->itemsPerPage;
		$this->startIndex++;
		if ($this->endIndex > $this->items) $this->endIndex = $this->items;
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'endIndex' => $this->endIndex,
			'entryFilter' => $this->entryFilter,
			'entryList' => $this->entryList,
			'entryType' => $this->entryType,
			'items' => $this->items,
			'itemsPerPage' => $this->itemsPerPage,
			'linkParameters' => $this->linkParameters,
			'pageNo' => $this->pageNo,
			'pages' => $this->pages,
			'pip' => $this->pip,
			'pipObject' => $this->pipObject,
			'project' => $this->project,
			'startIndex' => $this->startIndex
		]);
	}
}
