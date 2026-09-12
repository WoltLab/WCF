<?php

namespace wcf\system\cronjob;

use wcf\data\cronjob\Cronjob;
use wcf\data\cronjob\CronjobEditor;
use wcf\data\cronjob\log\CronjobLog;
use wcf\data\cronjob\log\CronjobLogBuilder;
use wcf\data\user\User;
use wcf\system\cache\builder\CronjobCacheBuilder;
use wcf\system\exception\ClassNotFoundException;
use wcf\system\exception\ImplementationException;
use wcf\system\exception\SystemException;
use wcf\system\session\SessionHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Provides functions to execute cronjobs.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class CronjobScheduler extends SingletonFactory
{
    /**
     * Upper bound for the runtime of a request that executes cronjobs. A cronjob that has been
     * executing for longer than this is assumed to have crashed.
     */
    private const CRASH_GRACE_PERIOD = 3600;

    /**
     * cached times of the next and after next cronjob execution
     * @var array{afterNextExec: int, nextExec: ?int}
     */
    private array $cache;

    #[\Override]
    protected function init()
    {
        $this->loadCache();
    }

    /**
     * Executes outstanding cronjobs.
     */
    public function executeCronjobs(): void
    {
        // break if there are no outstanding cronjobs
        if ($this->cache['nextExec'] > \TIME_NOW && $this->cache['afterNextExec'] > \TIME_NOW) {
            return;
        }

        $this->resetFailedCronjobs();

        // get outstanding cronjobs
        $cronjobEditors = $this->loadCronjobs();

        // clear cache
        self::clearCache();

        $user = WCF::getUser();
        try {
            SessionHandler::getInstance()->changeUser(User::getGuestUser(), true);

            foreach ($cronjobEditors as $cronjobEditor) {
                // Reset the memory usage for each cronjob, allowing to measure each individual
                // cronjob's memory usage without a memory-heavy cronjob skewing the numbers for
                // the following cronjobs.
                \memory_reset_peak_usage();

                // mark cronjob as being executed
                $cronjobEditor->update([
                    'state' => Cronjob::EXECUTING,
                ]);

                // create log entry
                $log = CronjobLogBuilder::forCreate()
                    ->setCronjobID($cronjobEditor->cronjobID)
                    ->setExecTime(\TIME_NOW)
                    ->create();

                // check if all required options are set for cronjob to be executed
                // note: a general log is created to avoid confusion why a cronjob
                // apparently is not executed while that is indeed the correct internal
                // behavior
                if ($cronjobEditor->validateOptions()) {
                    try {
                        $this->executeCronjob($cronjobEditor, $log);
                    } catch (\Throwable $e) {
                        $this->logResult($log, $e);
                    }
                } else {
                    $this->logResult($log);
                }

                // mark cronjob as done
                $cronjobEditor->update([
                    'failCount' => 0,
                    'state' => Cronjob::READY,
                ]);
            }
        } finally {
            SessionHandler::getInstance()->changeUser($user, true);
        }
    }

    /**
     * Returns the next execution time.
     */
    public function getNextExec(): ?int
    {
        return $this->cache['nextExec'];
    }

    /**
     * Resets any cronjobs that have previously failed to execute. Cronjobs that have failed too often will
     * be disabled automatically.
     */
    private function resetFailedCronjobs(): void
    {
        WCF::getDB()->beginTransaction();
        $committed = false;
        try {
            $sql = "SELECT      *
                    FROM        wcf1_cronjob
                    WHERE       state <> ?
                            AND isDisabled = ?
                            AND afterNextExec <= ?
                    FOR UPDATE";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                Cronjob::READY,
                0,
                \TIME_NOW,
            ]);

            // A crashed cronjob may be reached twice: through the pending cronjobs of its batch
            // and through its own timeout. The result set was locked at the start, so the second
            // visit would still see the stale state.
            $handledCronjobIDs = [];
            while ($cronjob = $statement->fetchObject(Cronjob::class)) {
                if (\in_array($cronjob->cronjobID, $handledCronjobIDs, true)) {
                    continue;
                }
                $handledCronjobIDs[] = $cronjob->cronjobID;

                switch ($cronjob->state) {
                    case Cronjob::EXECUTING:
                        // The cronjob spent two periods in the EXECUTING state.
                        // We must assume it crashed.
                        $this->resetCrashedCronjob($cronjob);
                        break;
                    case Cronjob::PENDING:
                        // The cronjob spent two periods in the PENDING state, which proves that the
                        // request that loaded it died before reaching it. This is not the fault of
                        // this cronjob, it is rescheduled without being reported.
                        $this->rescheduleCronjob($cronjob, ['state' => Cronjob::READY]);

                        // The cronjob that was executing when the request died is the one that
                        // crashed. All cronjobs of that request share the same `lastExec`, there is
                        // no need to wait for its own timeout.
                        //
                        // The timeout of this cronjob is derived from its own period, therefore it
                        // may elapse while the request is still working through the batch. Waiting
                        // for the grace period avoids reporting a slow cronjob as crashed, which
                        // would release its lock and permit a concurrent execution.
                        if ($cronjob->lastExec + self::CRASH_GRACE_PERIOD <= \TIME_NOW) {
                            foreach ($this->getCrashedCronjobs($cronjob->lastExec) as $crashedCronjob) {
                                if (\in_array($crashedCronjob->cronjobID, $handledCronjobIDs, true)) {
                                    continue;
                                }
                                $handledCronjobIDs[] = $crashedCronjob->cronjobID;

                                $this->resetCrashedCronjob($crashedCronjob);
                            }
                        }
                        break;
                    default:
                        throw new \LogicException('Unreachable');
                }
            }

            WCF::getDB()->commitTransaction();
            $committed = true;
        } finally {
            if (!$committed) {
                WCF::getDB()->rollBackTransaction();
            }
        }
    }

    /**
     * Reports a crashed cronjob and increases its fail counter. Cronjobs that have failed too often
     * will be disabled automatically.
     */
    private function resetCrashedCronjob(Cronjob $cronjob): void
    {
        $data = [
            'state' => Cronjob::READY,
            'failCount' => $cronjob->failCount + 1,
        ];

        // The cronjob exceeded the maximum fail count.
        // Cronjobs that can be disabled, should be disabled.
        if ($data['failCount'] >= Cronjob::MAX_FAIL_COUNT) {
            if ($cronjob->canBeDisabled !== 0) {
                $data['isDisabled'] = 1;
                $data['failCount'] = 0;
            } else {
                // Reset failCount for cronjobs, which can't be disabled to
                // MAX_FAIL_COUNT - 1, because the column has a max length
                // which should not be reached.
                $data['failCount'] = Cronjob::MAX_FAIL_COUNT - 1;
            }
        }

        $log = CronjobLogBuilder::forCreate()
            ->setCronjob($cronjob)
            ->setExecTime(\TIME_NOW)
            ->create();

        $errorMessage = \sprintf(
            "The cronjob '%s' (ID %d) did not finish and is assumed to have crashed. (lastExec %d, nextExec %d, afterNextExec %d, now %d)",
            $cronjob->cronjobName,
            $cronjob->cronjobID,
            $cronjob->lastExec,
            $cronjob->nextExec,
            $cronjob->afterNextExec,
            \TIME_NOW
        );
        $this->logResult($log, new \Exception($errorMessage));

        $this->rescheduleCronjob($cronjob, $data);
    }

    /**
     * Schedules the cronjob for execution at the next regular execution date. The previous
     * implementation was executing the cronjob immediately, which may be undesirable if
     * the cronjob is expected to be executed in a specific time window only.
     *
     * @param array<string, mixed> $data
     */
    private function rescheduleCronjob(Cronjob $cronjob, array $data): void
    {
        $data['nextExec'] = $cronjob->getNextExec(\TIME_NOW);
        $data['afterNextExec'] = $cronjob->getNextExec($data['nextExec'] + 120);

        (new CronjobEditor($cronjob))->update($data);
    }

    /**
     * Returns the cronjobs that were loaded by the same request as a cronjob with the given
     * `lastExec` and are still marked as executing.
     *
     * @return Cronjob[]
     */
    private function getCrashedCronjobs(int $lastExec): array
    {
        $sql = "SELECT  *
                FROM    wcf1_cronjob
                WHERE   state = ?
                    AND isDisabled = ?
                    AND lastExec = ?
                FOR UPDATE";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            Cronjob::EXECUTING,
            0,
            $lastExec,
        ]);

        return $statement->fetchObjects(Cronjob::class);
    }

    /**
     * Loads outstanding cronjobs.
     *
     * @return CronjobEditor[]
     */
    private function loadCronjobs(): array
    {
        WCF::getDB()->beginTransaction();
        $committed = false;
        try {
            $sql = "SELECT      *
                    FROM        wcf1_cronjob
                    WHERE       isDisabled = ?
                            AND state = ?
                            AND nextExec <= ?
                    ORDER BY    failCount ASC
                    FOR UPDATE";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                0,
                Cronjob::READY,
                \TIME_NOW,
            ]);

            $cronjobEditors = [];
            while ($cronjob = $statement->fetchObject(Cronjob::class)) {
                $cronjobEditor = new CronjobEditor($cronjob);

                // Mark the cronjob as pending to prevent concurrent requests from executing it.
                $data = ['state' => Cronjob::PENDING];

                // Update next execution time.
                // This needs to be done before executing the job to allow for a proper detection of
                // stuck jobs. If the timestamps are updated after executing then concurrent requests
                // might believe that a cronjob is stuck, despite the cronjob just having started just
                // a few milliseconds before.
                $data['nextExec'] = $cronjob->getNextExec();
                $data['afterNextExec'] = $cronjob->getNextExec($data['nextExec'] + 120);
                $data['lastExec'] = \TIME_NOW;

                $cronjobEditor->update($data);

                $cronjobEditors[] = $cronjobEditor;
            }
            WCF::getDB()->commitTransaction();
            $committed = true;

            return $cronjobEditors;
        } finally {
            if (!$committed) {
                WCF::getDB()->rollBackTransaction();
            }
        }
    }

    /**
     * Executes a cronjob.
     *
     * @throws  SystemException
     */
    private function executeCronjob(CronjobEditor $cronjobEditor, CronjobLog $log): void
    {
        $className = $cronjobEditor->className;
        if (!\class_exists($className)) {
            throw new ClassNotFoundException($className);
        }

        // verify class signature
        if (!\is_subclass_of($className, ICronjob::class)) {
            throw new ImplementationException($className, ICronjob::class);
        }

        // execute cronjob
        /** @var ICronjob $cronjob */
        $cronjob = new $className();
        $cronjob->execute($cronjobEditor->getDecoratedObject());

        $this->logResult($log);
    }

    /**
     * Logs cronjob exec success or failure.
     */
    private function logResult(CronjobLog $log, ?\Throwable $exception = null): void
    {
        if ($exception !== null) {
            \wcf\functions\exception\logThrowable($exception);

            $errString = \implode("\n", [
                $exception->getMessage(),
                $exception->getCode(),
                $exception->getFile(),
                $exception->getLine(),
                $exception->getTraceAsString(),
            ]);

            CronjobLogBuilder::forUpdate($log)
                ->setSuccess(false)
                ->setError($errString)
                ->update();
        } else {
            CronjobLogBuilder::forUpdate($log)
                ->setSuccess(true)
                ->update();
        }
    }

    /**
     * Loads the cached data for cronjob execution.
     */
    private function loadCache(): void
    {
        $this->cache = CronjobCacheBuilder::getInstance()->getData();
    }

    /**
     * Clears the cronjob data cache.
     */
    public static function clearCache(): void
    {
        CronjobCacheBuilder::getInstance()->reset();
    }
}
