<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\Callback;
use wcf\system\WCF;
use wcf\util\DirectoryUtil;
use wcf\util\FileUtil;

/**
 * Cronjob for a daily system cleanup.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cronjob
 */
class DailyCleanUpCronjob extends AbstractCronjob {
	/**
	 * @inheritDoc
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);
		
		// clean up search keywords
		$sql = "SELECT	AVG(searches) AS searches
			FROM	wcf".WCF_N."_search_keyword";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		if (($row = $statement->fetchArray()) !== false) {
			$sql = "DELETE FROM	wcf".WCF_N."_search_keyword
				WHERE		searches <= ?
						AND lastSearchTime < ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([
				floor($row['searches'] / 4),
				(TIME_NOW - 86400 * 30)
			]);
		}
		
		// clean up notifications
		$sql = "DELETE FROM	wcf".WCF_N."_user_notification
			WHERE		time < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			(TIME_NOW - 86400 * USER_CLEANUP_NOTIFICATION_LIFETIME)
		]);
		
		// clean up user activity events
		$sql = "DELETE FROM	wcf".WCF_N."_user_activity_event
			WHERE		time < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			(TIME_NOW - 86400 * USER_CLEANUP_ACTIVITY_EVENT_LIFETIME)
		]);
		
		// clean up profile visitors
		$sql = "DELETE FROM	wcf".WCF_N."_user_profile_visitor
			WHERE		time < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			(TIME_NOW - 86400 * USER_CLEANUP_PROFILE_VISITOR_LIFETIME)
		]);
		
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
			$statement1->execute([
				$objectType->objectTypeID,
				$lifetime
			]);
			$statement2->execute([
				$objectType->objectTypeID,
				$lifetime
			]);
		}
		WCF::getDB()->commitTransaction();
		
		// clean up cronjob log
		$sql = "DELETE FROM	wcf".WCF_N."_cronjob_log
			WHERE		execTime < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			(TIME_NOW - (86400 * 7))
		]);
		
		// clean up session access log
		$sql = "DELETE FROM	wcf".WCF_N."_acp_session_access_log
			WHERE		sessionLogID IN (
						SELECT	sessionLogID
						FROM	wcf".WCF_N."_acp_session_log
						WHERE	lastActivityTime < ?
					)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			(TIME_NOW - (86400 * 30))
		]);
		
		// clean up session log
		$sql = "DELETE FROM	wcf".WCF_N."_acp_session_log
			WHERE		lastActivityTime < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			(TIME_NOW - (86400 * 30))
		]);
		
		// clean up search data
		$sql = "DELETE FROM	wcf".WCF_N."_search
			WHERE		searchTime < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			(TIME_NOW - 86400)
		]);
		
		// clean up expired edit history entries
		if (MODULE_EDIT_HISTORY) {
			if (EDIT_HISTORY_EXPIRATION) {
				$sql = "DELETE FROM	wcf".WCF_N."_edit_history_entry
					WHERE		obsoletedAt < ?";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute([
					(TIME_NOW - 86400 * EDIT_HISTORY_EXPIRATION)
				]);
			}
		}
		else {
			// edit history is disabled, prune old versions
			$sql = "DELETE FROM	wcf".WCF_N."_edit_history_entry";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute();
		}
		
		// clean up user authentication failure log
		if (ENABLE_USER_AUTHENTICATION_FAILURE) {
			$sql = "DELETE FROM	wcf".WCF_N."_user_authentication_failure
				WHERE		time < ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([
				(TIME_NOW - 86400 * USER_AUTHENTICATION_FAILURE_EXPIRATION)
			]);
		}
		
		// clean up error logs
		$files = @glob(WCF_DIR.'log/*.txt');
		if (is_array($files)) {
			foreach ($files as $filename) {
				if (filectime($filename) < TIME_NOW - 86400 * 14) {
					@unlink($filename);
				}
			}
		}
		
		// clean up temporary folder
		$tempFolder = FileUtil::getTempFolder();
		DirectoryUtil::getInstance($tempFolder)->executeCallback(new Callback(function($filename, $object) use ($tempFolder) {
			if ($filename === $tempFolder) return;
			if ($filename === $tempFolder.'.htaccess') return;
			
			if ($object->getMTime() < TIME_NOW - 86400) {
				if ($object->isDir()) {
					@rmdir($filename);
				}
				else if ($object->isFile()) {
					@unlink($filename);
				}
			}
		}));
		
		// clean up proxy images
		if (MODULE_IMAGE_PROXY) {
			DirectoryUtil::getInstance(WCF_DIR.'images/proxy/')->executeCallback(new Callback(function($filename, $object) {
				if ($object->isFile() && $object->getMTime() < TIME_NOW - 86400 * IMAGE_PROXY_EXPIRATION) {
					@unlink($filename);
				}
			}));
		}
	}
}
