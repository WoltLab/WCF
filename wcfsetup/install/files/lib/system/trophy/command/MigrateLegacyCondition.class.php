<?php

namespace wcf\system\trophy\command;

use wcf\data\trophy\Trophy;
use wcf\data\trophy\TrophyEditor;
use wcf\system\condition\ConditionHandler;
use wcf\system\condition\provider\UserConditionProvider;
use wcf\system\exception\SystemException;
use wcf\util\JSON;

/**
 * Command to migrate legacy trophy conditions, to the new structure. *
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class MigrateLegacyCondition
{
    public function __construct(public readonly Trophy $trophy) {}

    public function __invoke(): void
    {
        if (!$this->trophy->isLegacy) {
            return;
        }

        try {
            $json = JSON::decode($this->trophy->conditions);
        } catch (SystemException $e) {
            // Side-effect: Logs the exception.
            $e->getExceptionID();

            return;
        }

        $migratedData = ConditionHandler::getInstance()->migrateConditionData(new UserConditionProvider(), $json);

        $editor = new TrophyEditor($this->trophy);
        $editor->update([
            'conditions' => JSON::encode($migratedData->conditions),
            'isLegacy' => 0,
            'isDisabled' => $migratedData->isFullyMigrated ? $this->trophy->isDisabled : 1,
        ]);
    }
}
