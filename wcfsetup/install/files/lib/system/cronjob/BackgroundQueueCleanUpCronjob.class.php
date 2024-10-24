<?php

namespace wcf\system\cronjob;

use wcf\data\cronjob\Cronjob;
use wcf\system\background\BackgroundQueueHandler;
use wcf\system\background\job\AbstractUniqueBackgroundJob;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Requeues stuck queue items.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
class BackgroundQueueCleanUpCronjob extends AbstractCronjob
{
    /**
     * @inheritDoc
     */
    public function execute(Cronjob $cronjob)
    {
        parent::execute($cronjob);

        WCF::getDB()->beginTransaction();
        /** @noinspection PhpUnusedLocalVariableInspection */
        $committed = false;
        /** @var AbstractUniqueBackgroundJob[] $uniqueJobs */
        $uniqueJobs = [];
        try {
            $sql = "SELECT      jobID, job
                    FROM        wcf1_background_job
                    WHERE       status = ?
                            AND time <= ?
                    ORDER BY    time ASC, jobID ASC
                    FOR UPDATE";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                'processing',
                TIME_NOW - 600, // running longer than 10 minutes
            ]);

            $jobIDs = [];
            while ($row = $statement->fetchArray()) {
                $jobIDs[] = $row['jobID'];

                try {
                    // no shut up operator, exception will be caught
                    $job = \unserialize($row['job']);
                    if ($job) {
                        $job->fail();

                        if ($job->getFailures() <= $job::MAX_FAILURES) {
                            BackgroundQueueHandler::getInstance()->enqueueIn($job, $job->retryAfter());
                        } else {
                            $job->onFinalFailure();

                            if ($job instanceof AbstractUniqueBackgroundJob) {
                                $uniqueJobs[] = $job;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // job is completely broken: log
                    \wcf\functions\exception\logThrowable($e);
                }
            }

            if (empty($jobIDs)) {
                WCF::getDB()->commitTransaction();
                $committed = true;

                return;
            }

            // delete jobs
            $condition = new PreparedStatementConditionBuilder();
            $condition->add('jobID IN (?)', [$jobIDs]);
            $sql = "DELETE FROM wcf1_background_job
                    " . $condition;
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($condition->getParameters());

            WCF::getDB()->commitTransaction();
            $committed = true;
        } finally {
            if (!$committed) {
                WCF::getDB()->rollBackTransaction();
            }
        }

        // Requeue unique jobs if needed
        foreach ($uniqueJobs as $job) {
            if ($job->queueAgain()) {
                BackgroundQueueHandler::getInstance()->enqueueIn($job->newInstance(), $job->retryAfter());
            }
        }
    }
}
