<?php

namespace wcf\system\condition;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
final class ConditionMigration
{
    private function __construct(
        public readonly bool $isFullyMigrated,
        /** @var array{identifier: string, value: mixed}[] */
        public readonly array $conditions,
    ) {
    }

    /**
     * Creates a new ConditionMigration instance based on condition data and conditions.
     *
     * @param array{identifier: string, value: mixed}[] $previousConditionData
     * @param array{identifier: string, value: mixed}[] $migratedConditionData
     */
    public static function fromData(array $previousConditionData, array $migratedConditionData): self
    {
        return new self($previousConditionData === [], $migratedConditionData);
    }

    /**
     * Creates a new ConditionMigration instance for empty data.
     */
    public static function withoutData(): self
    {
        return new self(true, []);
    }
}
