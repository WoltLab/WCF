<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\WCF;

/**
 * Removes moderation queue entries if they're done and older than 30 days.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cronjob
 */
class ModerationQueueCronjob extends AbstractCronjob {
	/**
	 * @inheritDoc
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);
		
		$sql = "SELECT	queueID
			FROM	wcf".WCF_N."_moderation_queue
			WHERE	status = ?
				AND lastChangeTime < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			ModerationQueue::STATUS_DONE,
			(TIME_NOW - (86400 * 30))
		]);
		$queueIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
		
		if (!empty($queueIDs)) {
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("queueID IN (?)", [$queueIDs]);
			
			$sql = "DELETE FROM	wcf".WCF_N."_moderation_queue
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			
			// reset moderation count for all users
			ModerationQueueManager::getInstance()->resetModerationCount();
		}
	}
}
