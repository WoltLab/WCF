<?php

namespace wcf\system\worker;

use wcf\data\user\group\assignment\UserGroupAssignmentList;
use wcf\system\user\group\assignment\command\MigrateLegacyCondition;

/**
 * Worker implementation for updating user group assignments.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractLinearRebuildDataWorker<UserGroupAssignmentList>
 */
final class UserGroupAssignmentRebuildDataWorker extends AbstractLinearRebuildDataWorker
{
    /**
     * @inheritDoc
     */
    protected $objectListClassName = UserGroupAssignmentList::class;

    /**
     * @inheritDoc
     */
    protected $limit = 100;

    #[\Override]
    public function execute()
    {
        parent::execute();

        foreach ($this->objectList as $assignment) {
            (new MigrateLegacyCondition($assignment))();
        }
    }
}
