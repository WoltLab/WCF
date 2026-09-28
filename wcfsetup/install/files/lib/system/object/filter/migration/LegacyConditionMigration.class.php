<?php

namespace wcf\system\object\filter\migration;

use wcf\data\object\type\ObjectType;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\registry\RegistryHandler;
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
 * Objects disabled by the migration are remembered and enabled again once a
 * later run, e.g. by the update of another package, has converted all their
 * remaining conditions. Objects that were already disabled are never enabled.
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

        $disabledObjectIDs = $this->getDisabledObjectIDs();

        foreach ($this->getConditionsByObject(\array_keys($objectTypes)) as $objectID => $object) {
            ['isDisabled' => $isDisabled, 'conditions' => $conditions] = $object;

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

            $newIsDisabled = null;
            if ($hasUnconvertedConditions) {
                if (!$isDisabled) {
                    $newIsDisabled = true;
                }
            } elseif (\in_array($objectID, $disabledObjectIDs, true)) {
                // All remaining conditions have been converted by this run.
                $newIsDisabled = false;
            }

            $this->updateObject($objectID, $filters, $convertedConditionIDs, $newIsDisabled);

            // Remembered after each object to keep the list in sync with the
            // committed changes if a later object fails.
            if ($newIsDisabled === true) {
                $disabledObjectIDs[] = $objectID;
                $this->setDisabledObjectIDs($disabledObjectIDs);
            } elseif ($newIsDisabled === false) {
                $disabledObjectIDs = \array_values(\array_diff($disabledObjectIDs, [$objectID]));
                $this->setDisabledObjectIDs($disabledObjectIDs);
            }
        }
    }

    /**
     * Returns a converter for conditions of `UserIntegerPropertyCondition`
     * that handles the given properties. Conditions of other properties are
     * left for the packages that provide them.
     *
     * The matching filter is `UserIntegerPropertyObjectFilter`, its bounds are
     * inclusive while the legacy bounds are exclusive.
     *
     * @param list<string> $propertyNames
     * @return \Closure(array<string, mixed>, ObjectType): ?list<array{0: string, 1: string}>
     */
    public static function getUserIntegerPropertyConverter(array $propertyNames): \Closure
    {
        return static function (array $data, ObjectType $objectType) use ($propertyNames): ?array {
            $propertyName = $objectType->propertyname;
            if (!\in_array($propertyName, $propertyNames, true)) {
                return null;
            }

            $from = isset($data['greaterThan']) ? (string)((int)$data['greaterThan'] + 1) : '';
            $to = isset($data['lessThan']) ? (string)((int)$data['lessThan'] - 1) : '';
            if ($from === '' && $to === '') {
                return null;
            }

            return [['com.woltlab.wcf.user' . \ucfirst($propertyName), $from . ';' . $to]];
        };
    }

    /**
     * Returns the ids of the objects that have been disabled by the migration.
     *
     * @return list<int>
     */
    private function getDisabledObjectIDs(): array
    {
        $value = RegistryHandler::getInstance()->get('com.woltlab.wcf', $this->getRegistryField());
        if ($value === null) {
            return [];
        }

        $objectIDs = \json_decode($value, true);
        if (!\is_array($objectIDs)) {
            return [];
        }

        return \array_values(\array_map(static fn($objectID) => (int)$objectID, $objectIDs));
    }

    /**
     * Stores the ids of the objects that have been disabled by the migration.
     *
     * @param list<int> $objectIDs
     */
    private function setDisabledObjectIDs(array $objectIDs): void
    {
        RegistryHandler::getInstance()->set(
            'com.woltlab.wcf',
            $this->getRegistryField(),
            \json_encode(\array_values(\array_unique($objectIDs)), \JSON_THROW_ON_ERROR)
        );
    }

    private function getRegistryField(): string
    {
        return 'legacyConditionMigration.' . $this->definitionName;
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
     * Returns the conditions of existing objects and whether the objects are
     * disabled, grouped by the id of the object.
     *
     * @param list<int> $objectTypeIDs
     * @return array<int, array{
     *  isDisabled: bool,
     *  conditions: list<array{conditionID: int, objectTypeID: int, conditionData: string}>,
     * }>
     */
    private function getConditionsByObject(array $objectTypeIDs): array
    {
        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('condition_table.objectTypeID IN (?)', [$objectTypeIDs]);

        // Conditions of deleted objects are skipped.
        $sql = "SELECT      condition_table.conditionID, condition_table.objectTypeID,
                            condition_table.objectID, condition_table.conditionData,
                            object_table.isDisabled
                FROM        wcf1_condition condition_table
                INNER JOIN  {$this->tableName} object_table
                ON          object_table.{$this->idColumn} = condition_table.objectID
                {$conditionBuilder}
                ORDER BY    condition_table.objectID, condition_table.conditionID";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());

        $objects = [];
        while ($row = $statement->fetchArray()) {
            $objects[$row['objectID']] ??= [
                'isDisabled' => (bool)$row['isDisabled'],
                'conditions' => [],
            ];
            $objects[$row['objectID']]['conditions'][] = [
                'conditionID' => $row['conditionID'],
                'objectTypeID' => $row['objectTypeID'],
                'conditionData' => $row['conditionData'],
            ];
        }

        return $objects;
    }

    /**
     * Appends the given filters to the object, deletes the converted legacy
     * conditions and updates the disabled state unless it is `null`.
     *
     * @param list<array{0: string, 1: string}> $filters
     * @param list<int> $convertedConditionIDs
     */
    private function updateObject(
        int $objectID,
        array $filters,
        array $convertedConditionIDs,
        ?bool $isDisabled
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

            if ($isDisabled !== null) {
                $sql = "UPDATE  {$this->tableName}
                        SET     isDisabled = ?
                        WHERE   {$this->idColumn} = ?";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([(int)$isDisabled, $objectID]);
            }

            WCF::getDB()->commitTransaction();
        } catch (\Throwable $e) {
            WCF::getDB()->rollBackTransaction();

            throw $e;
        }
    }
}
