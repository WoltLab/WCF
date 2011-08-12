<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;
use wcf\system\WCF;

/**
 * Deletes old entries from session log.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cronjob
 * @category 	Community Framework
 */
class CleanUpSessionLogCronjob implements ICronjob {
	/**
	 * @see wcf\system\ICronjob::execute()
	 */
	public function execute(Cronjob $cronjob) {
		// delete access log
		$sql = "DELETE FROM	wcf".WCF_N."_acp_session_access_log
			WHERE		sessionLogID IN (
						SELECT	sessionLogID
						FROM	wcf".WCF_N."_acp_session_log
						WHERE	lastActivityTime < ?
					)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			(TIME_NOW - (86400 * 30))
		));
		
		// delete session log
		$sql = "DELETE FROM	wcf".WCF_N."_acp_session_log
			WHERE		lastActivityTime < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			(TIME_NOW - (86400 * 30))
		));
	}
}
