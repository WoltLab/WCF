<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Cronjob for a daily system cleanup.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
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
		
		// clean up search keywords
		$sql = "SELECT 	AVG(searches) AS searches
			FROM	wcf".WCF_N."_search_keyword";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		if (($row = $statement->fetchArray()) !== false) {
			$sql = "DELETE FROM	wcf".WCF_N."_search_keyword
				WHERE		searches <= ?
						AND lastSearchTime < ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(
				floor($row['searches'] / 4),
				(TIME_NOW - 86400 * 30)
			));
		}
		
		// clean up notifications
		$sql = "DELETE FROM	wcf".WCF_N."_user_notification
			WHERE		time < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			(TIME_NOW - 86400 * USER_CLEANUP_NOTIFICATION_LIFETIME)
		));
		
		// clean up user activity events
		$sql = "DELETE FROM	wcf".WCF_N."_user_activity_event
			WHERE		time < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			(TIME_NOW - 86400 * USER_CLEANUP_ACTIVITY_EVENT_LIFETIME)
		));
		
		// clean up profile visitors
		$sql = "DELETE FROM	wcf".WCF_N."_user_profile_visitor
			WHERE		time < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			(TIME_NOW - 86400 * USER_CLEANUP_PROFILE_VISITOR_LIFETIME)
		));
		
		// tracked visits
		$sql = "DELETE FROM	wcf".WCF_N."_tracked_visit
			WHERE		objectTypeID = ?
					AND visitTime < ?";
		$statement1 = WCF::getDB()->prepareStatement($sql);
		$sql = "DELETE FROM	wcf".WCF_N."_tracked_visit_type
			WHERE		objectTypeID = ?
					AND visitTime < ?";
		$statement2 = WCF::getDB()->prepareStatement($sql);
		
		WCF::getDB()->beginTransaction();
		foreach (ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.visitTracker.objectType') as $objectType) {
			// get lifetime
			$lifetime = ($objectType->lifetime ?: VisitTracker::DEFAULT_LIFETIME);
				
			// delete data
			$statement1->execute(array(
				$objectType->objectTypeID,
				$lifetime
			));
			$statement2->execute(array(
				$objectType->objectTypeID,
				$lifetime
			));
		}
		WCF::getDB()->commitTransaction();
		
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
		
		// clean up error logs
		foreach (glob(WCF_DIR.'log/*.txt') as $filename) {
			if (filectime($filename) < TIME_NOW - 86400 * 14) {
				@unlink($filename);
			}
		}
	}
}
