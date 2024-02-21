<?php

namespace wcf\system\background;

use wcf\data\user\User;
use wcf\system\background\job\AbstractBackgroundJob;
use wcf\system\background\job\AbstractUniqueBackgroundJob;
use wcf\system\exception\ParentClassException;
use wcf\system\session\SessionHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Manages the background queue.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
final class BackgroundQueueHandler extends SingletonFactory
{
    public const FORCE_CHECK_HTTP_HEADER_NAME = 'woltlab-background-queue-check';

    public const FORCE_CHECK_HTTP_HEADER_VALUE = 'yes';

    private bool $hasPendingCheck = false;

    /**
     * Forces checking whether a background queue item is due.
     * This means that the AJAX request to BackgroundQueuePerformAction is triggered.
     */
    public function forceCheck(): void
    {
        WCF::getSession()->register('forceBackgroundQueuePerform', true);

        WCF::getTPL()->assign([
            'forceBackgroundQueuePerform' => true,
        ]);

        $this->hasPendingCheck = true;
    }

    /**
     * Enqueues the given job(s) for execution in the specified number of
     * seconds. Defaults to "as soon as possible" (0 seconds).
     *
     * @param AbstractBackgroundJob|AbstractBackgroundJob[] $jobs
     * @param $time Minimum number of seconds to wait before performing the job.
     * @see \wcf\system\background\BackgroundQueueHandler::enqueueAt()
     */
    public function enqueueIn(AbstractBackgroundJob|array $jobs, int $time = 0): void
    {
        $this->enqueueAt($jobs, TIME_NOW + $time);
    }

    /**
     * Enqueues the given job(s) for execution at the given time.
     * Note: The time is a minimum time. Depending on the size of
     * the queue the job can be performed later as well!
     *
     * @param AbstractBackgroundJob|AbstractBackgroundJob[] $jobs
     * @param $time Earliest time to consider the job for execution.
     * @throws  \InvalidArgumentException
     */
    public function enqueueAt(AbstractBackgroundJob|array $jobs, int $time): void
    {
        if ($time < TIME_NOW) {
            throw new \InvalidArgumentException("You may not schedule a job in the past (" . $time . " is smaller than the current timestamp " . TIME_NOW . ").");
        }
        if (!\is_array($jobs)) {
            $jobs = [$jobs];
        }

        foreach ($jobs as $job) {
            if (!($job instanceof AbstractBackgroundJob)) {
                throw new ParentClassException(\get_class($job), AbstractBackgroundJob::class);
            }
        }

        WCF::getDB()->beginTransaction();
        $sql = "INSERT INTO wcf1_background_job
                            (job, time)
                VALUES      (?, ?)";
        $statement = WCF::getDB()->prepare($sql);
        $sql = "SELECT jobID
                FROM   wcf1_background_job
                WHERE  identifier = ?
                FOR UPDATE";
        $selectJobStatement = WCF::getDB()->prepare($sql);

        foreach ($jobs as $job) {
            if ($job instanceof AbstractUniqueBackgroundJob) {
                // Check if the job is already in the queue
                $selectJobStatement->execute([$job->identifier()]);
                $jobID = $selectJobStatement->fetchSingleColumn();
                if ($jobID !== null) {
                    continue;
                }
            }

            $statement->execute([
                \serialize($job),
                $time,
            ]);
        }
        WCF::getDB()->commitTransaction();
    }

    /**
     * Immediately performs the given job.
     * This method automatically handles requeuing in case of failure.
     *
     * This method is used internally by performNextJob(), but it can
     * be useful if you wish immediate execution of a certain job, but
     * don't want to miss the automated error handling mechanism of the
     * queue.
     *
     * @param $debugSynchronousExecution Disables fail-safe mechanisms, errors will no longer be suppressed.
     * @throws  \Throwable
     */
    public function performJob(AbstractBackgroundJob $job, bool $debugSynchronousExecution = false): void
    {
        $user = WCF::getUser();

        try {
            SessionHandler::getInstance()->changeUser(new User(null), true);
            if (!WCF::debugModeIsEnabled()) {
                \ob_start();
            }
            $job->perform();
        } catch (\Throwable $e) {
            // do not suppress exceptions for debugging purposes, see https://github.com/WoltLab/WCF/issues/2501
            if ($debugSynchronousExecution) {
                throw $e;
            }

            $job->fail();

            if ($job->getFailures() <= $job::MAX_FAILURES) {
                $this->enqueueIn($job, $job->retryAfter());

                if (WCF::debugModeIsEnabled()) {
                    \wcf\functions\exception\logThrowable($e);
                }
            } else {
                $job->onFinalFailure();

                // job failed too often: log
                \wcf\functions\exception\logThrowable($e);
            }
        } finally {
            if (!WCF::debugModeIsEnabled()) {
                \ob_end_clean();
            }
            SessionHandler::getInstance()->changeUser($user, true);
        }
    }

    /**
     * Performs the (single) job that is due next.
     * This method automatically handles requeuing in case of failure.
     *
     * @return      bool         true if this call attempted to execute a job regardless of its result
     */
    public function performNextJob(): bool
    {
        WCF::getDB()->beginTransaction();
        $committed = false;
        try {
            $sql = "SELECT      jobID, job
                    FROM        wcf1_background_job
                    WHERE       status = ?
                            AND time <= ?
                    ORDER BY    time ASC, jobID ASC
                    FOR UPDATE";
            $statement = WCF::getDB()->prepare($sql, 1);
            $statement->execute([
                'ready',
                TIME_NOW,
            ]);
            $row = $statement->fetchSingleRow();
            if (!$row) {
                // nothing to do here
                return false;
            }

            // lock job
            $sql = "UPDATE  wcf1_background_job
                    SET     status = ?,
                            time = ?
                    WHERE   jobID = ?
                        AND status = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                'processing',
                TIME_NOW,
                $row['jobID'],
                'ready',
            ]);
            if ($statement->getAffectedRows() != 1) {
                // somebody stole the job
                // this cannot happen unless MySQL violates it's contract to lock the row
                // -> silently ignore, there will be plenty of other opportunities to perform a job
                return true;
            }
            WCF::getDB()->commitTransaction();
            $committed = true;
        } finally {
            if (!$committed) {
                WCF::getDB()->rollBackTransaction();
            }
        }

        $job = null;
        try {
            // no shut up operator, exception will be caught
            $job = \unserialize($row['job']);
            if ($job) {
                $this->performJob($job);
            }
        } catch (\Throwable $e) {
            // job is completely broken: log
            \wcf\functions\exception\logThrowable($e);
        } finally {
            // remove entry of processed job
            $sql = "DELETE FROM wcf1_background_job
                    WHERE       jobID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$row['jobID']]);
        }

        return true;
    }

    /**
     * Returns how many items are due.
     *
     * Note: Do not rely on the return value being correct, some other process may
     * have modified the queue contents, before this method returns. Think of it as an
     * approximation to know whether you should spend some time to clear the queue.
     */
    public function getRunnableCount(): int
    {
        $sql = "SELECT  COUNT(*)
                FROM    wcf1_background_job
                WHERE   status = ?
                    AND time <= ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute(['ready', TIME_NOW]);

        return $statement->fetchSingleColumn();
    }

    /**
     * Indicates that the client should trigger a check for
     * pending jobs in the background queue.
     *
     * @since 6.0
     */
    public function hasPendingCheck(): bool
    {
        return $this->hasPendingCheck;
    }
}
