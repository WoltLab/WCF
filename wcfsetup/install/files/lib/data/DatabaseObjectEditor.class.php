<?php

namespace wcf\data;

use wcf\system\database\exception\DatabaseQueryExecutionException;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Basic implementation for object editors following the decorator pattern.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @template TDatabaseObject of DatabaseObject
 * @extends DatabaseObjectDecorator<TDatabaseObject>
 * @implements IEditableObject<TDatabaseObject>
 */
abstract class DatabaseObjectEditor extends DatabaseObjectDecorator implements IEditableObject
{
    #[\Override]
    public static function create(array $parameters = [])
    {
        return new static::$baseClass(static::fastCreate($parameters));
    }

    #[\Override]
    public function update(array $parameters = [])
    {
        if ($parameters === []) {
            return;
        }

        $updateSQL = '';
        $statementParameters = [];
        foreach ($parameters as $key => $value) {
            if (!empty($updateSQL)) {
                $updateSQL .= ', ';
            }
            $updateSQL .= $key . ' = ?';
            $statementParameters[] = $value;
        }
        $statementParameters[] = $this->getObjectID();

        $sql = "UPDATE  " . static::getDatabaseTableName() . "
                SET     " . $updateSQL . "
                WHERE   " . static::getDatabaseTableIndexName() . " = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($statementParameters);
    }

    #[\Override]
    public function updateCounters(array $counters = [])
    {
        if ($counters === []) {
            return;
        }

        $updateSQL = '';
        $statementParameters = [];
        foreach ($counters as $key => $value) {
            if (!empty($updateSQL)) {
                $updateSQL .= ', ';
            }
            $updateSQL .= $key . ' = ' . $key . ' + ?';
            $statementParameters[] = $value;
        }
        $statementParameters[] = $this->getObjectID();

        $sql = "UPDATE  " . static::getDatabaseTableName() . "
                SET     " . $updateSQL . "
                WHERE   " . static::getDatabaseTableIndexName() . " = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($statementParameters);
    }

    #[\Override]
    public function delete()
    {
        static::deleteAll([$this->getObjectID()]);
    }

    #[\Override]
    public static function deleteAll(array $objectIDs = [])
    {
        $affectedCount = 0;

        $itemsPerLoop = 1000;
        $loopCount = \ceil(\count($objectIDs) / $itemsPerLoop);

        WCF::getDB()->beginTransaction();
        for ($i = 0; $i < $loopCount; $i++) {
            $batchObjectIDs = \array_slice($objectIDs, $i * $itemsPerLoop, $itemsPerLoop);

            $conditionBuilder = new PreparedStatementConditionBuilder();
            $conditionBuilder->add(static::getDatabaseTableIndexName() . ' IN (?)', [$batchObjectIDs]);

            $sql = "DELETE FROM " . static::getDatabaseTableName() . "
                    " . $conditionBuilder;
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($conditionBuilder->getParameters());
            $affectedCount += $statement->getAffectedRows();
        }
        WCF::getDB()->commitTransaction();

        return $affectedCount;
    }

    /**
     * Creates a new object, returns null if the row already exists.
     *
     * @param array<string, mixed> $parameters
     * @return ?TDatabaseObject
     * @since 5.3
     */
    public static function createOrIgnore(array $parameters = [])
    {
        try {
            return static::create($parameters);
        } catch (DatabaseQueryExecutionException $e) {
            // Error code 23000 = duplicate key
            if ((int)$e->getCode() === 23000 && (int)$e->getDriverCode() === 1062) {
                return null;
            }

            throw $e;
        }
    }

    /**
     * Creates the object and returns the ID.
     *
     * @param array<string, mixed> $parameters
     */
    public static function fastCreate(array $parameters): int|string
    {
        $keys = $values = '';
        $statementParameters = [];
        foreach ($parameters as $key => $value) {
            if (!empty($keys)) {
                $keys .= ',';
                $values .= ',';
            }

            $keys .= $key;
            $values .= '?';
            $statementParameters[] = $value;
        }

        // save object
        $sql = "INSERT INTO " . static::getDatabaseTableName() . "
                            (" . $keys . ")
                VALUES      (" . $values . ")";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($statementParameters);

        // return new object
        if (static::getDatabaseTableIndexIsIdentity()) {
            $id = WCF::getDB()->getInsertID(static::getDatabaseTableName(), static::getDatabaseTableIndexName());
        } else {
            $id = $parameters[static::getDatabaseTableIndexName()];
        }

        return $id;
    }
}
