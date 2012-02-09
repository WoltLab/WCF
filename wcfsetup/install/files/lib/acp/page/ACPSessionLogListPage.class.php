<?php
namespace wcf\acp\page;
use wcf\page\SortablePage;
use wcf\system\menu\acp\ACPMenu;
use wcf\system\WCF;

/**
 * Shows a list of log sessions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class ACPSessionLogListPage extends SortablePage {
	/**
	 * @see wcf\page\AbstractPage::$templateName
	 */
	public $templateName = 'acpSessionLogList';
	
	/**
	 * @see wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.canViewLog');
	
	/**
	 * @see wcf\page\SortablePage::$defaultSortField
	 */
	public $defaultSortField = 'lastActivityTime';
	
	/**
	 * @see wcf\page\SortablePage::$defaultSortOrder
	 */
	public $defaultSortOrder = 'DESC';
	
	/**
	 * @see wcf\page\SortablePage::$validSortFields
	 */
	public $validSortFields = array('sessionLogID', 'username', 'ipAddress', 'userAgent', 'time', 'lastActivityTime', 'accesses');
	
	/**
	 * @see	wcf\page\MultipleLinkPage::$objectListClassName
	 */
	public $objectListClassName = 'wcf\data\acp\session\log\ACPSessionLogList';
	
	/**
	 * @see	wcf\page\MultipleLinkPage::readObjects()
	 */
	public function readObjects() {
		$this->sqlOrderBy = (($this->sortField != 'accesses' && $this->sortField != 'username') ? 'acp_session_log.' : '').$this->sortField." ".$this->sortOrder;
		
		parent::readObjects();
	}
	
	/**
	 * @see wcf\page\IPage::show()
	 */
	public function show() {
		// enable menu item
		ACPMenu::getInstance()->setActiveMenuItem('wcf.acp.menu.link.log.session');
		
		parent::show();
	}
}
