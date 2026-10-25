<?php

namespace wcf\data\cronjob;

use wcf\command\cronjob\DeleteCronjob;
use wcf\command\cronjob\DisableCronjob;
use wcf\command\cronjob\EnableCronjob;
use wcf\command\cronjob\ExecuteCronjob;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;
use wcf\data\user\User;
use wcf\system\cronjob\CronjobScheduler;
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
 * @extends AbstractDatabaseObjectAction<Cronjob, CronjobEditor>
 * @deprecated 6.3 use `CronjobBuilder` with the `CreateCronjob` / `UpdateCronjob` commands instead.
 */
class CronjobAction extends AbstractDatabaseObjectAction implements IToggleAction
{
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

    #[\Override]
    public function validateDelete()
    {
        parent::validateDelete();

        foreach ($this->getObjects() as $cronjob) {
            if (!$cronjob->isDeletable()) {
                throw new PermissionDeniedException();
            }
        }
    }

    #[\Override]
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
     * @deprecated 6.3
     */
    #[\Override]
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
     * @deprecated 6.3 use the `DeleteCronjob` command instead.
     */
    #[\Override]
    public function delete()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        foreach ($this->objects as $object) {
            new DeleteCronjob($object->getDecoratedObject())();
        }

        return \count($this->objects);
    }

    /**
     * Validates the 'execute' action.
     *
     * @return void
     */
    public function validateExecute()
    {
        parent::validateUpdate();
    }

    /**
     * Executes cronjobs.
     *
     * @return array<int, array{
     *  time: int,
     *  formatted: string,
     * }>
     * @deprecated 6.3 use the `ExecuteCronjob` command instead.
     */
    public function execute()
    {
        $return = [];

        foreach ($this->getObjects() as $editor) {
            $cronjob = new ExecuteCronjob($editor->getDecoratedObject())();

            if ($cronjob->isDisabled === 0) {
                $dateTime = DateUtil::getDateTimeByTimestamp($cronjob->nextExec);
                $return[$cronjob->cronjobID] = [
                    'time' => $cronjob->nextExec,
                    'formatted' => \IntlDateFormatter::create(
                        WCF::getLanguage()->getLocale(),
                        \IntlDateFormatter::LONG,
                        \IntlDateFormatter::SHORT,
                        WCF::getUser()->getTimeZone()
                    )->format($dateTime),
                ];
            }
        }

        return $return;
    }

    /**
     * @return void
     * @deprecated 6.0 Either use CronjobScheduler::executeCronjobs() directly, or query CronjobPerformAction.
     */
    public function validateExecuteCronjobs()
    {
        // does nothing
    }

    /**
     * @return void
     * @deprecated 6.0 Either use CronjobScheduler::executeCronjobs() directly, or query CronjobPerformAction.
     */
    public function executeCronjobs()
    {
        // switch session owner to 'system' during execution of cronjobs
        WCF::getSession()->changeUser(User::getGuestUser('System'), true);
        WCF::getSession()->disableUpdate();

        CronjobScheduler::getInstance()->executeCronjobs();
    }

    /**
     * @deprecated 6.3 use the `EnableCronjob` or `DisableCronjob` commands instead.
     */
    #[\Override]
    public function toggle()
    {
        foreach ($this->objects as $editor) {
            if ($editor->isDisabled !== 0) {
                new EnableCronjob($editor->getDecoratedObject())();
            } else {
                new DisableCronjob($editor->getDecoratedObject())();
            }
        }
    }
}
