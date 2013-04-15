<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;
use wcf\system\WCF;

/**
 * Cronjob for a daily system cleanup.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cronjob
 * @category	Community Framework
 */
class DailyCleanUpCronjob extends AbstractCronjob {
	/**
	 * @see	wcf\system\cronjob\ICronjob::execute()
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);
		
		// clean up cronjob log
		$sql = "DELETE FROM	wcf".WCF_N."_cronjob_log
			WHERE		execTime < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			(TIME_NOW - (86400 * 7))
		));
		
		// clean up session access log
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
		
		// clean up session log
		$sql = "DELETE FROM	wcf".WCF_N."_acp_session_log
			WHERE		lastActivityTime < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			(TIME_NOW - (86400 * 30))
		));
		
		// clean up search data
		$sql = "DELETE FROM	wcf".WCF_N."_search
			WHERE		searchTime < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			(TIME_NOW - 86400)
		));
	}
}
