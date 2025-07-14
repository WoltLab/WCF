<?php

namespace wcf\system\condition\type;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
interface IMigrateConditionType
{
    /**
     * Migrates old condition data to the new condition format by removing all successfully migrated entries from the `$conditionData`
     * and returns a list of condition-data in the new structure. The remaining entries are assumed to be unprocessed and are handled
     * by other condition types and must remain untouched.
     *
     * Note:
     * - Remove entries that you have successfully migrated.
     * - Leave unrecognized or unsupported entries untouched.
     * - If no data can be migrated, return an empty array.
     *
     * This allows `ConditionHandler::migrateConditionData()` to check whether all data has been migrated correctly and completely.
     *
     * @param array<string, mixed> $conditionData
     *
     * @return list<array{identifier: string, value: mixed}>
     */
    public function migrateConditionData(array &$conditionData): array;

    /**
     * Returns `true` if the method `migrateConditionData()` can migrate data for the given `$objectType` and `false` otherwise.
     */
    public function canMigrateConditionData(string $objectType): bool;
}
