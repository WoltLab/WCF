<?php
namespace wcf\system\background;
use wcf\system\background\job\AbstractJob;
use wcf\system\exception\LoggedException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Manages the background queue.
 *
 * @author	Tim Duesterhus
 * @copyright	2001 - 2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.background.job
 * @category	Community Framework
 */
class BackgroundQueueHandler extends SingletonFactory {
	/**
	 * Enqueues the given job for execution at the given time.
	 * Note: The time is a minimum time. Depending on the size of
	 * the queue the job can be performed later as well!
	 *
	 * @param	\wcf\system\background\job\AbstractJob	$job	The job to enqueue.
	 * @param	int					$time	Earliest time to consider the job for execution.
	 */
	public function enqueue(AbstractJob $job, $time = 0) {
		$sql = "INSERT INTO	wcf".WCF_N."_background_job
					(job, time)
			VALUES		(?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			serialize($job),
			$time
		]);
	}

	/**
	 * Performs the (single) job that is due next.
	 * This method automatically handles requeuing in case of failure.
	 */
	public function performJob() {
		WCF::getDB()->beginTransaction();
		$commited = false;
		try {
			$sql = "SELECT		jobID, job
				FROM		wcf".WCF_N."_background_job
				WHERE		status = ?
					AND	time <= ?
				ORDER BY	time ASC, jobID ASC
				FOR UPDATE";
			$statement = WCF::getDB()->prepareStatement($sql, 1);
			$statement->execute([
				'ready',
				TIME_NOW
			]);
			$row = $statement->fetchSingleRow();
			if (!$row) {
				// nothing to do here
				return;
			}
			
			// lock job
			$sql = "UPDATE	wcf".WCF_N."_background_job
				SET	status = ?,
					time = ?
				WHERE		jobID = ?
					AND	status = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([
				'processing',
				TIME_NOW,
				$row['jobID'],
				'ready'
			]);
			if ($statement->getAffectedRows() != 1) {
				// somebody stole the job
				// this cannot happen unless MySQL violates it's contract to lock the row
				// -> silently ignore, there will be plenty of other oppurtunities to perform a job
				return;
			}
			WCF::getDB()->commitTransaction();
			$commited = true;
		}
		finally {
			if (!$commited) WCF::getDB()->rollbackTransaction();
		}

		$job = null;
		try {
			// no shut up operator, exception will be caught
			$job = unserialize($row['job']);
			if ($job) {
				$job->perform();
			}
		}
		catch (\Exception $e) {
			// gotta catch 'em all
			
			if ($job) {
				$job->fail();
				
				if ($job->getFailures() <= $job::MAX_FAILURES) {
					$this->enqueue($job, TIME_NOW + $job->retryAfter());
				}
				else {
					// job failed too often: log
					if ($e instanceof LoggedException) $e->getExceptionID();
				}
			}
			else {
				// job is completely broken: log
				if ($e instanceof LoggedException) $e->getExceptionID();
			}
		}
		finally {
			// remove entry of processed job
			
			$sql = "DELETE FROM	wcf".WCF_N."_background_job
				WHERE		jobID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([ $row['jobID'] ]);
		}
	}
}
