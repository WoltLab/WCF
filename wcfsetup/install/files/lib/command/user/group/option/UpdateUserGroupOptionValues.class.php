<?php

namespace wcf\command\user\group\option;

use wcf\data\user\group\option\UserGroupOption;
use wcf\data\user\group\UserGroupEditor;
use wcf\event\user\group\option\UserGroupOptionValuesUpdated;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\event\EventHandler;
use wcf\system\WCF;

/**
 * Updates the values of a user group option.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class UpdateUserGroupOptionValues
{
    public function __construct(
        private readonly UserGroupOption $option,
        /** @var array<int, int|float|string> */
        private readonly array $groupIDToValue = []
    ) {}

    public function __invoke(): void
    {
        $groupIDs = \array_keys($this->groupIDToValue);

        $this->deleteOldValues($this->option->optionID, $groupIDs);

        $this->insertValues($this->option->optionID, $this->groupIDToValue);

        UserGroupEditor::resetCache();

        $event = new UserGroupOptionValuesUpdated($this->option, $this->groupIDToValue);
        EventHandler::getInstance()->fire($event);
    }

    /**
     * @param list<int> $groupIDs
     */
    private function deleteOldValues(int $optionID, array $groupIDs): void
    {
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add('optionID = ?', [$optionID]);
        if ($groupIDs !== []) {
            $conditions->add('groupID IN (?)', [$groupIDs]);
        }

        $sql = "DELETE FROM wcf1_user_group_option_value
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());
    }

    /**
     * @param array<int, int|float|string> $groupIDToValue
     */
    private function insertValues(int $optionID, array $groupIDToValue): void
    {
        $sql = "INSERT INTO wcf1_user_group_option_value
                            (optionID, groupID, optionValue)
                VALUES      (?, ?, ?)";
        $statement = WCF::getDB()->prepare($sql);

        WCF::getDB()->beginTransaction();
        foreach ($groupIDToValue as $groupID => $optionValue) {
            $statement->execute([
                $optionID,
                $groupID,
                $optionValue,
            ]);
        }
        WCF::getDB()->commitTransaction();
    }
}
