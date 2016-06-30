<?php
namespace wcf\acp\page;
use wcf\data\application\Application;
use wcf\data\application\ApplicationList;
use wcf\data\page\PageList;
use wcf\page\SortablePage;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows a list of pages.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * @since	3.0
 *
 * @property	PageList	$objectList
 */
class PageListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.cms.page.list';
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = PageList::class;
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.content.cms.canManagePage'];
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'name';
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['pageID', 'name', 'lastUpdateTime', 'pageType'];
	
	/**
	 * @inheritDoc
	 */
	public $itemsPerPage = 50;
	
	/**
	 * name
	 * @var	string
	 */
	public $name = '';
	
	/**
	 * title
	 * @var	string
	 */
	public $title = '';
	
	/**
	 * content
	 * @var	string
	 */
	public $content = '';
	
	/**
	 * application id of the page
	 * @var integer
	 */
	public $applicationPackageID = 0;
	
	/**
	 * page type
	 * @var string
	 */
	public $pageType = '';
	
	/**
	 * list of available applications
	 * @var Application[]
	 */
	public $availableApplications = [];
	
	/**
	 * display 'Add Page' dialog on load
	 * @var integer
	 */
	public $showPageAddDialog = 0;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!empty($_REQUEST['name'])) $this->name = StringUtil::trim($_REQUEST['name']);
		if (!empty($_REQUEST['title'])) $this->title = StringUtil::trim($_REQUEST['title']);
		if (!empty($_REQUEST['content'])) $this->content = StringUtil::trim($_REQUEST['content']);
		if (isset($_REQUEST['applicationPackageID'])) $this->applicationPackageID = intval($_REQUEST['applicationPackageID']);
		if (!empty($_REQUEST['pageType'])) $this->pageType = $_REQUEST['pageType'];
		if (!empty($_REQUEST['showPageAddDialog'])) $this->showPageAddDialog = 1;
		
		// get available applications
		$applicationList = new ApplicationList();
		$applicationList->readObjects();
		$this->availableApplications = $applicationList->getObjects();
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		if (!empty($this->name)) {
			$this->objectList->getConditionBuilder()->add('page.name LIKE ?', ['%'.$this->name.'%']);
		}
		if (!empty($this->title)) {
			$this->objectList->getConditionBuilder()->add('page.pageID IN (SELECT pageID FROM wcf'.WCF_N.'_page_content WHERE title LIKE ?)', ['%'.$this->title.'%']);
		}
		if (!empty($this->content)) {
			$this->objectList->getConditionBuilder()->add('page.pageID IN (SELECT pageID FROM wcf'.WCF_N.'_page_content WHERE content LIKE ?)', ['%'.$this->content.'%']);
		}
		if (!empty($this->applicationPackageID)) {
			$this->objectList->getConditionBuilder()->add('page.applicationPackageID = ?', [$this->applicationPackageID]);
		}
		if (!empty($this->pageType)) {
			$this->objectList->getConditionBuilder()->add('page.pageType = (?)', [$this->pageType]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'name' => $this->name,
			'title' => $this->title,
			'content' => $this->content,
			'applicationPackageID' => $this->applicationPackageID,
			'pageType' => $this->pageType,
			'availableApplications' => $this->availableApplications,
			'showPageAddDialog' => $this->showPageAddDialog
		]);
	}
}
