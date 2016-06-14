<?php
namespace wcf\acp\page;
use wcf\data\acp\session\access\log\ACPSessionAccessLogList;
use wcf\data\acp\session\log\ACPSessionLog;
use wcf\page\SortablePage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Shows the details of a logged sessions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * 
 * @property	ACPSessionAccessLogList		$objectList
 */
class ACPSessionLogPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.log.session';
	
	/**
	 * @inheritDoc
	 */
	public $templateName = 'acpSessionLog';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.management.canViewLog'];
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'time';
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['sessionAccessLogID', 'ipAddress', 'time', 'requestURI', 'requestMethod', 'className'];
	
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
	 * @inheritDoc
	 */
	public $objectListClassName = ACPSessionAccessLogList::class;
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->getConditionBuilder()->add('sessionLogID = ?', [$this->sessionLogID]);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function readObjects() {
		$this->sqlOrderBy = 'acp_session_access_log.'.$this->sortField." ".$this->sortOrder;
		
		parent::readObjects();
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'sessionLogID' => $this->sessionLogID,
			'sessionLog' => $this->sessionLog
		]);
	}
}
