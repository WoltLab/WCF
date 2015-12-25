<?php
namespace wcf\acp\page;
use wcf\data\media\ViewableMediaList;
use wcf\page\SortablePage;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\WCF;

/**
 * Shows the list of media entries.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 * @since	2.2
 */
class MediaListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.media.list';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'uploadTime';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortOrder = 'DESC';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.content.cms.canManageMedia'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = ViewableMediaList::class;
	
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
		
		WCF::getTPL()->assign('hasMarkedItems', ClipboardHandler::getInstance()->hasMarkedItems(ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.media')));
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
