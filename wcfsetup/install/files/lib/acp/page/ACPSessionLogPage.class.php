<?php
namespace wcf\acp\page;
use wcf\data\acp\session\log\ACPSessionLog;
use wcf\page\SortablePage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Shows the details of a logged sessions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 */
class ACPSessionLogPage extends SortablePage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.log.session';
	
	/**
	 * @see	\wcf\page\AbstractPage::$templateName
	 */
	public $templateName = 'acpSessionLog';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.canViewLog');
	
	/**
	 * @see	\wcf\page\SortablePage::$defaultSortField
	 */
	public $defaultSortField = 'time';
	
	/**
	 * @see	\wcf\page\SortablePage::$validSortFields
	 */
	public $validSortFields = array('sessionAccessLogID', 'ipAddress', 'time', 'requestURI', 'requestMethod', 'className');
	
	/**
	 * session log id
	 * @var	integer
	 */
	public $sessionLogID = 0;
	
	/**
	 * session log object
	 * @var	\wcf\data\acp\session\log\ACPSessionLog
	 */
	public $sessionLog = null;
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$objectListClassName
	 */
	public $objectListClassName = 'wcf\data\acp\session\access\log\ACPSessionAccessLogList';
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get session log
		if (isset($_REQUEST['id'])) $this->sessionLogID = intval($_REQUEST['id']);
		$this->sessionLog = new ACPSessionLog($this->sessionLogID);
		if (!$this->sessionLog->sessionLogID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::initObjectList()
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->getConditionBuilder()->add('sessionLogID = ?', array($this->sessionLogID));
	}
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::readObjects()
	 */
	protected function readObjects() {
		$this->sqlOrderBy = 'acp_session_access_log.'.$this->sortField." ".$this->sortOrder;
		
		parent::readObjects();
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'sessionLogID' => $this->sessionLogID,
			'sessionLog' => $this->sessionLog
		));
	}
}
