<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;
use wcf\system\background\BackgroundQueueHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Requeues stuck queue items.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cronjob
 * @since	3.0
 */
class BackgroundQueueCleanUpCronjob extends AbstractCronjob {
	/**
	 * @inheritDoc
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);
		
		WCF::getDB()->beginTransaction();
		/** @noinspection PhpUnusedLocalVariableInspection */
		$commited = false;
		try {
			$sql = "SELECT		jobID, job
				FROM		wcf".WCF_N."_background_job
				WHERE		status = ?
					AND	time <= ?
				ORDER BY	time ASC, jobID ASC
				FOR UPDATE";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([
				'processing',
				TIME_NOW - 600 // running longer than 10 minutes
			]);
			
			$jobIDs = [];
			while ($row = $statement->fetchArray()) {
				$jobIDs[] = $row['jobID'];
				
				try {
					// no shut up operator, exception will be caught
					$job = unserialize($row['job']);
					if ($job) {
						$job->fail();
						
						if ($job->getFailures() <= $job::MAX_FAILURES) {
							BackgroundQueueHandler::getInstance()->enqueueIn($job, $job->retryAfter());
						}
					}
				}
				catch (\Exception $e) {
					// job is completely broken: log
					\wcf\functions\exception\logThrowable($e);
				}
			}
			
			if (empty($jobIDs)) {
				WCF::getDB()->commitTransaction();
				$commited = true;
				return;
			}
			
			// delete jobs
			$condition = new PreparedStatementConditionBuilder();
			$condition->add('jobID IN (?)', [$jobIDs]);
			$sql = "DELETE FROM	wcf".WCF_N."_background_job ".$condition;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($condition->getParameters());
			
			WCF::getDB()->commitTransaction();
			$commited = true;
		}
		finally {
			if (!$commited) WCF::getDB()->rollBackTransaction();
		}
	}
}
