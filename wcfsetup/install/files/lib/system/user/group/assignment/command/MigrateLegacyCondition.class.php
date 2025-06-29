<?php

namespace wcf\system\user\group\assignment\command;

use wcf\data\user\group\assignment\UserGroupAssignment;
use wcf\data\user\group\assignment\UserGroupAssignmentEditor;
use wcf\system\condition\ConditionHandler;
use wcf\system\condition\provider\UserConditionProvider;
use wcf\util\JSON;

/**
 * Command to migrate legacy user group assignment conditions, to the new structure.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class MigrateLegacyCondition
{
    public function __construct(
        public readonly UserGroupAssignment $assignment,
    ) {
    }

    public function __invoke(): void
    {
        if (!$this->assignment->isLegacy) {
            return;
        }

        $migratedData = ConditionHandler::getInstance()->migrateConditionData(
            new UserConditionProvider(),
            JSON::decode($this->assignment->conditions)
        );

        $editor = new UserGroupAssignmentEditor($this->assignment);
        $editor->update([
            'conditions' => JSON::encode($migratedData->conditions),
            'isLegacy' => 0,
            'isDisabled' => $migratedData->isFullyMigrated ? $this->assignment->isDisabled : 1,
        ]);
    }
}
