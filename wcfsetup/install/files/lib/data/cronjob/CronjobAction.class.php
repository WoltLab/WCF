<?php

namespace wcf\data\cronjob;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\cronjob\log\CronjobLogEditor;
use wcf\data\IToggleAction;
use wcf\data\TDatabaseObjectToggle;
use wcf\data\user\User;
use wcf\system\cronjob\CronjobScheduler;
use wcf\system\cronjob\ICronjob;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Executes cronjob-related actions.
 *
 * @author  Tim Duesterhus, Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  Cronjob         create()
 * @method  CronjobEditor[]     getObjects()
 * @method  CronjobEditor       getSingleObject()
 */
class CronjobAction extends AbstractDatabaseObjectAction implements IToggleAction
{
    use TDatabaseObjectToggle;

    /**
     * @inheritDoc
     */
    protected $className = CronjobEditor::class;

    /**
     * @inheritDoc
     */
    protected $permissionsCreate = ['admin.management.canManageCronjob'];

    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.management.canManageCronjob'];

    /**
     * @inheritDoc
     */
    protected $permissionsUpdate = ['admin.management.canManageCronjob'];

    /**
     * @inheritDoc
     */
    protected $allowGuestAccess = ['executeCronjobs'];

    /**
     * @inheritDoc
     */
    protected $requireACP = ['create', 'delete', 'update', 'toggle', 'execute'];

    /**
     * @inheritDoc
     */
    public function validateDelete()
    {
        parent::validateDelete();

        foreach ($this->getObjects() as $cronjob) {
            if (!$cronjob->isDeletable()) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function validateUpdate()
    {
        parent::validateUpdate();

        foreach ($this->getObjects() as $cronjob) {
            if (!$cronjob->isEditable()) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function validateToggle()
    {
        parent::validateUpdate();

        foreach ($this->getObjects() as $cronjob) {
            if (!$cronjob->canBeDisabled()) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * Validates the 'execute' action.
     */
    public function validateExecute()
    {
        parent::validateUpdate();
    }

    /**
     * Executes cronjobs.
     */
    public function execute()
    {
        $return = [];

        // switch session owner to 'system' during execution of cronjobs
        $actualUser = WCF::getUser();
        WCF::getSession()->changeUser(new User(null, ['userID' => 0, 'username' => 'System']), true);
        WCF::getSession()->disableUpdate();

        try {
            foreach ($this->getObjects() as $cronjob) {
                // mark them as pending
                $cronjob->update(['state' => Cronjob::PENDING]);
            }

            foreach ($this->getObjects() as $cronjob) {
                // it now time for executing
                $cronjob->update(['state' => Cronjob::EXECUTING]);
                $className = $cronjob->className;

                /** @var ICronjob $executable */
                $executable = new $className();

                // execute cronjob
                $exception = null;

                // check if all required options are set for cronjob to be executed
                // note: a general log is created to avoid confusion why a cronjob
                // apparently is not executed while that is indeed the correct internal
                // behavior
                if ($cronjob->validateOptions()) {
                    try {
                        $executable->execute(new Cronjob($cronjob->cronjobID));
                    } catch (\Exception $exception) {
                    }
                }

                CronjobLogEditor::create([
                    'cronjobID' => $cronjob->cronjobID,
                    'execTime' => TIME_NOW,
                    'success' => $exception ? 0 : 1,
                    'error' => $exception ? \mb_substr($exception->getMessage(), 0, 65000) : '',
                ]);

                // calculate next exec-time
                $nextExec = $cronjob->getNextExec();
                $data = [
                    'lastExec' => TIME_NOW,
                    'nextExec' => $nextExec,
                    'afterNextExec' => $cronjob->getNextExec($nextExec + 120),
                ];

                // cronjob failed
                if ($exception) {
                    if ($cronjob->failCount < Cronjob::MAX_FAIL_COUNT) {
                        $data['failCount'] = $cronjob->failCount + 1;
                    }

                    // cronjob failed too often: disable it
                    if ($cronjob->failCount + 1 == Cronjob::MAX_FAIL_COUNT) {
                        $data['isDisabled'] = 1;
                    }
                } // if no error: reset fail counter
                else {
                    $data['failCount'] = 0;

                    // if cronjob has been disabled because of too many
                    // failed executions, enable it again
                    if ($cronjob->failCount == Cronjob::MAX_FAIL_COUNT && $cronjob->isDisabled) {
                        $data['isDisabled'] = 0;
                    }
                }

                $cronjob->update($data);

                // build the return value
                if ($exception === null && !$cronjob->isDisabled) {
                    $dateTime = DateUtil::getDateTimeByTimestamp($nextExec);
                    $return[$cronjob->cronjobID] = [
                        'time' => $nextExec,
                        'formatted' => \IntlDateFormatter::create(
                            WCF::getLanguage()->getLocale(),
                            \IntlDateFormatter::LONG,
                            \IntlDateFormatter::SHORT,
                            WCF::getUser()->getTimeZone()
                        )->format($dateTime),
                    ];
                }

                // we are finished
                $cronjob->update(['state' => Cronjob::READY]);

                // throw exception again to show error message
                if ($exception) {
                    throw $exception;
                }
            }
        } finally {
            // switch session back to the actual user
            WCF::getSession()->changeUser($actualUser, true);
        }

        return $return;
    }

    /**
     * @deprecated 6.0 Either use CronjobScheduler::executeCronjobs() directly, or query CronjobPerformAction.
     */
    public function validateExecuteCronjobs()
    {
        // does nothing
    }

    /**
     * @deprecated 6.0 Either use CronjobScheduler::executeCronjobs() directly, or query CronjobPerformAction.
     */
    public function executeCronjobs()
    {
        // switch session owner to 'system' during execution of cronjobs
        WCF::getSession()->changeUser(new User(null, ['userID' => 0, 'username' => 'System']), true);
        WCF::getSession()->disableUpdate();

        CronjobScheduler::getInstance()->executeCronjobs();
    }
}
