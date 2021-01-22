<?php
namespace wcf\data\acp\session\log;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of session log entries.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Acp\Session\Log
 *
 * @method	ACPSessionLog		current()
 * @method	ACPSessionLog[]		getObjects()
 * @method	ACPSessionLog|null	search($objectID)
 * @property	ACPSessionLog[]		$objects
 */
class ACPSessionLogList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = ACPSessionLog::class;
	
	/**
	 * @inheritDoc
	 */
	public function readObjects() {
		if (!empty($this->sqlSelects)) $this->sqlSelects .= ',';
		$this->sqlSelects .= "	user_table.username,
					0 AS active,
					(SELECT COUNT(*) FROM wcf".WCF_N."_acp_session_access_log WHERE sessionLogID = ".$this->getDatabaseTableAlias().".sessionLogID) AS accesses";
		
		$this->sqlJoins .= "	LEFT JOIN wcf".WCF_N."_user user_table ON (user_table.userID = ".$this->getDatabaseTableAlias().".userID)";
		
		parent::readObjects();
	}
}
