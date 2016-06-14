<?php
namespace wcf\acp\page;
use wcf\data\notice\NoticeList;
use wcf\page\SortablePage;

/**
 * Lists the available notices.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * 
 * @property	NoticeList	$objectList
 */
class NoticeListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.notice.list';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'showOrder';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.notice.canManageNotice'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = NoticeList::class;
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['noticeID', 'noticeName', 'showOrder'];
}
