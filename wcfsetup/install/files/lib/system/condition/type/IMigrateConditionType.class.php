<?php

namespace wcf\system\condition\type;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
interface IMigrateConditionType
{
    /**
     * Converts the old condition structure to the new one. All migrated values must be removed from the `$conditionData` array.
     *
     * @param array<string, mixed> $conditionData
     *
     * @return array{identifier: string, value: mixed}[]
     */
    public function migrateConditionData(array &$conditionData): array;

    public function canMigrateConditionData(string $objectType): bool;
}
