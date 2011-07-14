<?php
namespace wcf\acp\page;
use wcf\system\menu\acp\ACPMenu;
use wcf\data\acp\session\log\ACPSessionLog;
use wcf\page\SortablePage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Shows the details of a logged sessions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class ACPSessionLogPage extends SortablePage {
	/**
	 * @see wcf\page\AbstractPage::$templateName
	 */
	public $templateName = 'acpSessionLog';
	
	/**
	 * @see wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.canViewLog');
	
	/**
	 * @see wcf\page\SortablePage::$defaultSortField
	 */
	public $defaultSortField = 'time';
	
	/**
	 * session log id
	 *
	 * @var integer
	 */
	public $sessionLogID = 0;
	
	/**
	 * session log object
	 *
	 * @var wcf\data\acp\session\log\ACPSessionLog
	 */
	public $sessionLog = null;
	
	/**
	 * @see	wcf\page\MultipleLinkPage::$objectListClassName
	 */
	public $objectListClassName = 'wcf\data\acp\session\access\log\ACPSessionAccessLogList';
	
	/**
	 * @see wcf\page\Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get session log
		if (isset($_REQUEST['sessionLogID'])) $this->sessionLogID = intval($_REQUEST['sessionLogID']);
		$this->sessionLog = new ACPSessionLog($this->sessionLogID);
		if (!$this->sessionLog->sessionLogID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see	wcf\page\MultipleLinkPage::initObjectList()
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->getConditionBuilder()->add('sessionLogID = ?', array($this->sessionLogID));
	}
	
	/**
	 * @see	wcf\page\MultipleLinkPage::readObjects()
	 */	
	protected function readObjects() {
		$this->sqlOrderBy = ($this->sortField != 'packageName' ? 'acp_session_access_log.' : '').$this->sortField." ".$this->sortOrder;
		
		parent::readObjects();
	}
	
	/**
	 * @see wcf\page\SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'sessionAccessLogID':
			case 'ipAddress':
			case 'time':
			case 'requestURI':
			case 'requestMethod':
			case 'className':
			case 'packageName': break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
	
	/**
	 * @see wcf\page\Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'sessionAccessLogs' => $this->objectList->getObjects(),
			'sessionLogID' => $this->sessionLogID,
			'sessionLog' => $this->sessionLog
		));
	}
	
	/**
	 * @see wcf\page\Page::show()
	 */
	public function show() {
		// enable menu item
		ACPMenu::getInstance()->setActiveMenuItem('wcf.acp.menu.link.log.session');
		
		parent::show();
	}
}
?>
