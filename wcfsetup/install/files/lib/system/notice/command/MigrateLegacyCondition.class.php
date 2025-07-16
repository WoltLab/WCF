<?php

namespace wcf\system\notice\command;

use wcf\data\notice\Notice;
use wcf\data\notice\NoticeEditor;
use wcf\system\condition\ConditionHandler;
use wcf\system\condition\provider\combined\NoticeConditionProvider;
use wcf\system\exception\SystemException;
use wcf\util\JSON;

/**
 * Command to migrate legacy notice conditions, to the new structure.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class MigrateLegacyCondition
{
    public function __construct(public readonly Notice $notice)
    {
    }

    public function __invoke(): void
    {
        if (!$this->notice->isLegacy) {
            return;
        }

        try {
            $json = JSON::decode($this->notice->conditions);
        } catch (SystemException $ex) {
            $ex->getExceptionID(); // Log the exception if JSON decoding fails

            return;
        }

        $migratedData = ConditionHandler::getInstance()->migrateConditionData(new NoticeConditionProvider(), $json);

        $editor = new NoticeEditor($this->notice);
        $editor->update([
            'conditions' => JSON::encode($migratedData->conditions),
            'isLegacy' => 0,
            'isDisabled' => $migratedData->isFullyMigrated ? $this->notice->isDisabled : 1,
        ]);
    }
}
