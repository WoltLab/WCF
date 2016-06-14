<?php
namespace wcf\acp\page;
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
 * @copyright	2001-2016 WoltLab GmbH
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
	public $activeMenuItem = 'wcf.acp.menu.link.cms.media.list';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'uploadTime';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortOrder = 'DESC';
	
	/**
	 * searched media filename
	 * @var	string
	 */
	public $filename = '';
	
	/**
	 * searched media file type
	 * @var	string
	 */
	public $fileType = 'all';
	
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
		'uploadTime'
	];
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'filename' => $this->filename,
			'fileType' => $this->fileType,
			'hasMarkedItems' => ClipboardHandler::getInstance()->hasMarkedItems(ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.media')),
			'username' => $this->username
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		if ($this->filename) {
			$this->objectList->addSearchConditions($this->filename);
		}
		if ($this->fileType) {
			$this->objectList->addDefaultFileTypeFilter($this->fileType);
		}
		if ($this->username) {
			$this->objectList->getConditionBuilder()->add('media.username LIKE ?', ['%'.addcslashes($this->username, '_%').'%']);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['filename'])) $this->filename = StringUtil::trim($_REQUEST['filename']);
		if (isset($_REQUEST['fileType'])) $this->fileType = StringUtil::trim($_REQUEST['fileType']);
		if (isset($_REQUEST['username'])) $this->username = StringUtil::trim($_REQUEST['username']);
		
		if ($this->fileType == 'all') $this->fileType = '';
		
		$parameters = [];
		if ($this->sortField) $parameters['sortField'] = $this->sortField;
		if ($this->sortOrder) $parameters['sortOrder'] = $this->sortOrder;
		if ($this->filename) $parameters['filename'] = $this->filename;
		if ($this->fileType) $parameters['fileType'] = $this->fileType;
		if ($this->username) $parameters['username'] = $this->username;
		
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
