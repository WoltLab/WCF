<?php

namespace wcf\command\cronjob;

use wcf\data\cronjob\Cronjob;
use wcf\data\cronjob\CronjobBuilder;
use wcf\data\cronjob\log\CronjobLogBuilder;
use wcf\data\user\User;
use wcf\system\cache\builder\CronjobCacheBuilder;
use wcf\system\cronjob\ICronjob;
use wcf\system\WCF;

/**
 * Executes a cronjob immediately, regardless of its schedule, and returns the
 * updated cronjob. An exception thrown by the cronjob is logged and rethrown.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class ExecuteCronjob
{
    public function __construct(
        private readonly Cronjob $cronjob,
    ) {}

    public function __invoke(): Cronjob
    {
        // switch session owner to 'system' during execution of the cronjob
        $actualUser = WCF::getUser();
        WCF::getSession()->changeUser(User::getGuestUser('System'), true);
        WCF::getSession()->disableUpdate();

        try {
            return $this->execute();
        } finally {
            // switch session back to the actual user
            WCF::getSession()->changeUser($actualUser, true);

            CronjobCacheBuilder::getInstance()->reset();
        }
    }

    private function execute(): Cronjob
    {
        CronjobBuilder::forUpdate($this->cronjob)
            ->setState(Cronjob::PENDING)
            ->update();

        $cronjob = CronjobBuilder::forUpdate($this->cronjob)
            ->setState(Cronjob::EXECUTING)
            ->update();

        // check if all required options are set for cronjob to be executed
        // note: a general log is created to avoid confusion why a cronjob
        // apparently is not executed while that is indeed the correct internal
        // behavior
        $exception = null;
        if ($cronjob->validateOptions()) {
            try {
                /** @var ICronjob $executable */
                $executable = new ($cronjob->className)();
                $executable->execute($cronjob);
            } catch (\Exception $exception) {
            }
        }

        CronjobLogBuilder::forCreate()
            ->setCronjobID($cronjob->cronjobID)
            ->setExecTime(\TIME_NOW)
            ->setSuccess($exception === null)
            ->setError($exception?->getMessage())
            ->create();

        $nextExec = $cronjob->getNextExec();
        $builder = CronjobBuilder::forUpdate($cronjob)
            ->setLastExec(\TIME_NOW)
            ->setNextExec($nextExec)
            ->setAfterNextExec($cronjob->getNextExec($nextExec + 120))
            ->setState(Cronjob::READY);

        if ($exception !== null) {
            if ($cronjob->failCount < Cronjob::MAX_FAIL_COUNT) {
                $builder->setFailCount($cronjob->failCount + 1);
            }

            // cronjob failed too often: disable it
            if ($cronjob->failCount + 1 === Cronjob::MAX_FAIL_COUNT) {
                $builder->setIsDisabled(true);
            }
        } else {
            $builder->setFailCount(0);

            // if cronjob has been disabled because of too many
            // failed executions, enable it again
            if ($cronjob->failCount === Cronjob::MAX_FAIL_COUNT && $cronjob->isDisabled !== 0) {
                $builder->setIsDisabled(false);
            }
        }

        $cronjob = $builder->update();

        // throw exception again to show error message
        if ($exception !== null) {
            throw $exception;
        }

        return $cronjob;
    }
}
