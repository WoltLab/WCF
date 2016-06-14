<?php
namespace wcf\acp\page;
use wcf\data\acp\session\log\ACPSessionLogList;
use wcf\page\SortablePage;

/**
 * Shows a list of log sessions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * 
 * @property	ACPSessionLogList	$objectList
 */
class ACPSessionLogListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.log.session';
	
	/**
	 * @inheritDoc
	 */
	public $templateName = 'acpSessionLogList';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.management.canViewLog'];
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'lastActivityTime';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortOrder = 'DESC';
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['sessionLogID', 'username', 'ipAddress', 'userAgent', 'time', 'lastActivityTime', 'accesses'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = ACPSessionLogList::class;
	
	/**
	 * @inheritDoc
	 */
	public function readObjects() {
		$this->sqlOrderBy = (($this->sortField != 'accesses' && $this->sortField != 'username') ? 'acp_session_log.' : '').$this->sortField." ".$this->sortOrder;
		
		parent::readObjects();
	}
}
