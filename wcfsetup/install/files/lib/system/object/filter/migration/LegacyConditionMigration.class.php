<?php

namespace wcf\system\object\filter\migration;

use wcf\data\object\type\ObjectType;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Migrates the conditions of the legacy condition system (`wcf1_condition`)
 * into the JSON-encoded object filters stored in the `conditions` column of
 * the objects the conditions belong to.
 *
 * Only conditions whose object type has a converter and whose data could be
 * converted are migrated and deleted. All other conditions are left untouched,
 * allowing other packages to migrate them later. Objects with remaining legacy
 * conditions are disabled, as evaluating only a part of their conditions would
 * match more users than intended.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 * @deprecated  6.3 Only intended for the update to 6.3, will be removed with the next major version.
 */
final class LegacyConditionMigration
{
    /**
     * @param string $definitionName name of the object type definition of the conditions, e.g. `com.woltlab.wcf.condition.notice`
     * @param string $tableName name of the table of the objects, must have the columns `conditions` and `isDisabled`
     * @param string $idColumn name of the id column of the table
     */
    public function __construct(
        private readonly string $definitionName,
        private readonly string $tableName,
        private readonly string $idColumn,
    ) {}

    /**
     * Migrates the conditions using the given converters, indexed by the class
     * name of the condition object type.
     *
     * A converter receives the unserialized condition data and the object type
     * and returns the list of `[filterIdentifier, serializedValue]` pairs that
     * replace the condition, or `null` if the condition cannot be converted.
     *
     * @param array<string, callable(array<string, mixed>, ObjectType): ?list<array{0: string, 1: string}>> $converters
     */
    public function migrate(array $converters): void
    {
        $objectTypes = $this->getObjectTypes();
        if ($objectTypes === []) {
            return;
        }

        foreach ($this->getConditionsByObject(\array_keys($objectTypes)) as $objectID => $conditions) {
            $filters = [];
            $convertedConditionIDs = [];
            $hasUnconvertedConditions = false;

            foreach ($conditions as $condition) {
                $objectType = $objectTypes[$condition['objectTypeID']];
                $converter = $converters[$objectType->className] ?? null;

                $result = null;
                $conditionData = @\unserialize($condition['conditionData']);
                if ($converter !== null && \is_array($conditionData)) {
                    try {
                        $result = $converter($conditionData, $objectType);
                    } catch (\Throwable) {
                        // Malformed legacy data must not break the update,
                        // the condition is treated as not convertible.
                        $result = null;
                    }
                }

                if ($result === null) {
                    $hasUnconvertedConditions = true;
                } else {
                    \array_push($filters, ...$result);
                    $convertedConditionIDs[] = $condition['conditionID'];
                }
            }

            $this->updateObject($objectID, $filters, $convertedConditionIDs, $hasUnconvertedConditions);
        }
    }

    /**
     * Returns the condition object types of the definition, indexed by their id.
     *
     * @return array<int, ObjectType>
     */
    private function getObjectTypes(): array
    {
        $sql = "SELECT      object_type.*
                FROM        wcf1_object_type object_type
                INNER JOIN  wcf1_object_type_definition definition
                ON          definition.definitionID = object_type.definitionID
                WHERE       definition.definitionName = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->definitionName]);

        $objectTypes = [];
        while ($row = $statement->fetchArray()) {
            $objectTypes[$row['objectTypeID']] = new ObjectType(null, $row);
        }

        return $objectTypes;
    }

    /**
     * Returns the conditions of existing objects, grouped by the id of the object.
     *
     * @param list<int> $objectTypeIDs
     * @return array<int, list<array{conditionID: int, objectTypeID: int, conditionData: string}>>
     */
    private function getConditionsByObject(array $objectTypeIDs): array
    {
        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('condition_table.objectTypeID IN (?)', [$objectTypeIDs]);

        // Conditions of deleted objects are skipped.
        $sql = "SELECT      condition_table.conditionID, condition_table.objectTypeID,
                            condition_table.objectID, condition_table.conditionData
                FROM        wcf1_condition condition_table
                INNER JOIN  {$this->tableName} object_table
                ON          object_table.{$this->idColumn} = condition_table.objectID
                {$conditionBuilder}
                ORDER BY    condition_table.objectID, condition_table.conditionID";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());

        $conditions = [];
        while ($row = $statement->fetchArray()) {
            $conditions[$row['objectID']][] = [
                'conditionID' => $row['conditionID'],
                'objectTypeID' => $row['objectTypeID'],
                'conditionData' => $row['conditionData'],
            ];
        }

        return $conditions;
    }

    /**
     * Appends the given filters to the object, deletes the converted legacy
     * conditions and disables the object if some conditions are left.
     *
     * @param list<array{0: string, 1: string}> $filters
     * @param list<int> $convertedConditionIDs
     */
    private function updateObject(
        int $objectID,
        array $filters,
        array $convertedConditionIDs,
        bool $hasUnconvertedConditions
    ): void {
        WCF::getDB()->beginTransaction();
        try {
            if ($filters !== []) {
                $sql = "SELECT  conditions
                        FROM    {$this->tableName}
                        WHERE   {$this->idColumn} = ?
                        FOR UPDATE";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([$objectID]);
                $existingFilters = \json_decode((string)$statement->fetchSingleColumn(), true);

                // Keeps filters that were migrated by an earlier run, e.g. by another package.
                if (\is_array($existingFilters)) {
                    $filters = [...$existingFilters, ...$filters];
                }

                $sql = "UPDATE  {$this->tableName}
                        SET     conditions = ?
                        WHERE   {$this->idColumn} = ?";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([
                    \json_encode($filters, \JSON_THROW_ON_ERROR),
                    $objectID,
                ]);
            }

            if ($convertedConditionIDs !== []) {
                $conditionBuilder = new PreparedStatementConditionBuilder();
                $conditionBuilder->add('conditionID IN (?)', [$convertedConditionIDs]);

                $sql = "DELETE FROM wcf1_condition
                        {$conditionBuilder}";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute($conditionBuilder->getParameters());
            }

            if ($hasUnconvertedConditions) {
                $sql = "UPDATE  {$this->tableName}
                        SET     isDisabled = ?
                        WHERE   {$this->idColumn} = ?";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([1, $objectID]);
            }

            WCF::getDB()->commitTransaction();
        } catch (\Throwable $e) {
            WCF::getDB()->rollBackTransaction();

            throw $e;
        }
    }
}
