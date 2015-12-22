<?php
namespace wcf\acp\page;
use wcf\data\media\ViewableMediaList;
use wcf\page\SortablePage;

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
	 * @inheritdoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.media.list';
	
	/**
	 * @inheritdoc
	 */
	public $defaultSortField = 'uploadTime';
	
	/**
	 * @inheritdoc
	 */
	public $defaultSortOrder = 'DESC';
	
	/**
	 * @inheritdoc
	 */
	public $neededPermissions = ['admin.content.cms.canManageMedia'];
	
	/**
	 * @inheritdoc
	 */
	public $objectListClassName = ViewableMediaList::class;
	
	/**
	 * @inheritdoc
	 */
	public $validSortFields = [
		'filename',
		'filesize',
		'mediaID',
		'title',
		'uploadTime'
	];
	
	/**
	 * @inheritdoc
	 */
	protected function readObjects() {
		if ($this->sqlOrderBy && $this->sortField == 'mediaID') {
			$this->sqlOrderBy = 'media.'.$this->sortField.' '.$this->sortOrder;
		}
		
		parent::readObjects();
	}
}
