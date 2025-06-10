<?php

namespace wcf\system\user\group\assignment\command;

use wcf\data\user\group\assignment\UserGroupAssignment;
use wcf\data\user\group\assignment\UserGroupAssignmentEditor;
use wcf\system\condition\ConditionHandler;
use wcf\system\condition\provider\UserConditionProvider;
use wcf\util\JSON;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class UserGroupAssignmentMigrateCondition
{
    public function __construct(
        public readonly UserGroupAssignment $assignment,
    ) {
    }

    public function __invoke(): void
    {
        if (!$this->assignment->needMigration) {
            return;
        }

        $migratedData = ConditionHandler::getInstance()->migrateConditionData(
            new UserConditionProvider(),
            JSON::decode($this->assignment->conditions)
        );

        $editor = new UserGroupAssignmentEditor($this->assignment);
        $editor->update([
            'conditions' => JSON::encode($migratedData->conditions),
            'needMigration' => 0,
            'isDisabled' => $migratedData->isFullMigrated ? $this->assignment->isDisabled : 1,
        ]);
    }
}
