<?php
namespace wcf\acp\page;
use wcf\data\category\CategoryNodeTree;
use wcf\data\media\ViewableMediaList;
use wcf\page\SortablePage;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the list of media entries.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * @since	3.0
 * 
 * @property	ViewableMediaList	$objectList
 */
class MediaListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.media.list';
	
	/**
	 * id of the selected media category
	 * @var	integer
	 */
	public $categoryID = 0;
	
	/**
	 * node tree with all available media categories
	 * @var	\RecursiveIteratorIterator
	 */
	public $categoryList;
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'uploadTime';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortOrder = 'DESC';
	
	/**
	 * searched media query
	 * @var	string
	 */
	public $query = '';
	
	/**
	 * @inheritDoc
	 */
	public $forceCanonicalURL = true;
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.content.cms.canManageMedia'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = ViewableMediaList::class;
	
	/**
	 * name of the user who uploaded the searched media files
	 * @var	string
	 */
	public $username = '';
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = [
		'filename',
		'filesize',
		'mediaID',
		'title',
		'uploadTime',
		'downloads',
		'lastDownloadTime'
	];
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'categoryID' => $this->categoryID,
			'categoryList' => $this->categoryList,
			'q' => $this->query,
			'hasMarkedItems' => ClipboardHandler::getInstance()->hasMarkedItems(ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.media')),
			'username' => $this->username
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		if (WCF::getSession()->getPermission('admin.content.cms.canOnlyAccessOwnMedia')) {
			$this->objectList->getConditionBuilder()->add('media.userID = ?', [WCF::getUser()->userID]);
		}
		
		if ($this->categoryID) {
			$this->objectList->getConditionBuilder()->add('media.categoryID = ?', [$this->categoryID]);
		}
		if ($this->query) {
			$this->objectList->addSearchConditions($this->query);
		}
		if ($this->username) {
			$this->objectList->getConditionBuilder()->add('media.username LIKE ?', ['%'.addcslashes($this->username, '_%').'%']);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		$this->categoryList = (new CategoryNodeTree('com.woltlab.wcf.media.category'))->getIterator();
		$this->categoryList->setMaxDepth(0);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['categoryID'])) $this->categoryID = intval($_REQUEST['categoryID']);
		if (isset($_REQUEST['q'])) $this->query = StringUtil::trim($_REQUEST['q']);
		if (isset($_REQUEST['username'])) $this->username = StringUtil::trim($_REQUEST['username']);
		
		$parameters = [];
		if ($this->sortField) $parameters['sortField'] = $this->sortField;
		if ($this->sortOrder) $parameters['sortOrder'] = $this->sortOrder;
		if ($this->query) $parameters['q'] = $this->query;
		if ($this->username) $parameters['username'] = $this->username;
		if ($this->categoryID) $parameters['categoryID'] = $this->categoryID;
		
		$this->canonicalURL = LinkHandler::getInstance()->getLink('MediaList', $parameters);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function readObjects() {
		if ($this->sqlOrderBy && $this->sortField == 'mediaID') {
			$this->sqlOrderBy = 'media.'.$this->sortField.' '.$this->sortOrder;
		}
		
		parent::readObjects();
	}
}
