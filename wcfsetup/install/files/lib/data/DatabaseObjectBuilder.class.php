<?php

namespace wcf\data;

use wcf\system\database\exception\DatabaseQueryExecutionException;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\ClassNotFoundException;
use wcf\system\exception\ImplementationException;
use wcf\system\WCF;

/**
 * Abstract builder for creating, updating and deleting database objects.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @template TDatabaseObject of DatabaseObject
 */
abstract class DatabaseObjectBuilder
{
    /**
     * @var array<string, string|int|float|null>
     */
    protected array $properties = [];

    /**
     * @var array<string, string|int|float|null>
     */
    protected array $customProperties = [];

    /**
     * @var array<string, int|float>
     */
    protected array $incrementProperties = [];

    /**
     * Use forCreate() or forUpdate() to obtain a builder instance.
     *
     * @param ?TDatabaseObject $object
     */
    private function __construct(protected readonly ?DatabaseObject $object = null) {}

    /**
     * Inserts a new row and returns the created database object.
     *
     * @return TDatabaseObject
     */
    final public function create(): DatabaseObject
    {
        if ($this->object !== null) {
            throw new \BadMethodCallException("create() can only be used with forCreate().");
        }

        $this->validateCreate();
        $this->afterValidateCreate();

        $keys = $values = '';
        $statementParameters = [];
        foreach (\array_merge($this->properties, $this->customProperties, $this->incrementProperties) as $key => $value) {
            if ($keys !== '') {
                $keys .= ',';
                $values .= ',';
            }

            $keys .= $key;
            $values .= '?';
            $statementParameters[] = $value;
        }

        $sql = "INSERT INTO " . static::getBaseClass()::getDatabaseTableName() . "
                            (" . $keys . ")
                VALUES      (" . $values . ")";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($statementParameters);

        if (isset($this->properties[static::getBaseClass()::getDatabaseTableIndexName()])) {
            $id = $this->properties[static::getBaseClass()::getDatabaseTableIndexName()];
        } elseif (static::getBaseClass()::getDatabaseTableIndexIsIdentity()) {
            $id = WCF::getDB()->getInsertID(static::getBaseClass()::getDatabaseTableName(), static::getBaseClass()::getDatabaseTableIndexName());
        } else {
            throw new \BadMethodCallException("Missing value for '" . static::getBaseClass()::getDatabaseTableIndexName() . "'");
        }

        $object = new (static::getBaseClass())($id);

        $this->afterCreate($object);

        return $object;
    }

    /**
     * Validates that the pending changes are sufficient to create a new object.
     *
     * @throws \BadMethodCallException if no properties are set or a required property is missing
     */
    private function validateCreate(): void
    {
        if ($this->properties === [] && $this->customProperties === [] && $this->incrementProperties === []) {
            throw new \BadMethodCallException("Cannot create an object without any properties.");
        }

        foreach ($this->getRequiredProperties() as $property) {
            if (!\array_key_exists($property, $this->properties) && !\array_key_exists($property, $this->incrementProperties)) {
                throw new \BadMethodCallException("Missing value for required property '{$property}'.");
            }
        }
    }

    /**
     * Returns the names of the properties that must be set when creating a new
     * object. Subclasses can override this method to enforce that required
     * values are provided before the object is persisted.
     *
     * @return list<string>
     */
    protected function getRequiredProperties(): array
    {
        return [];
    }

    /**
     * Writes the pending property changes to the existing row.
     *
     * @return TDatabaseObject
     */
    final public function update(): DatabaseObject
    {
        if ($this->object === null) {
            throw new \BadMethodCallException("update() can only be used with forUpdate().");
        }

        if ($this->properties !== [] || $this->customProperties !== [] || $this->incrementProperties !== []) {
            $updateSQL = '';
            $statementParameters = [];
            foreach (\array_merge($this->properties, $this->customProperties) as $key => $value) {
                if ($updateSQL !== '') {
                    $updateSQL .= ', ';
                }
                $updateSQL .= $key . ' = ?';
                $statementParameters[] = $value;
            }
            foreach ($this->incrementProperties as $key => $value) {
                if ($updateSQL !== '') {
                    $updateSQL .= ', ';
                }

                $updateSQL .= \sprintf(
                    '%s = %s + ?',
                    $key,
                    $key,
                );
                $statementParameters[] = $value;
            }
            $statementParameters[] = $this->object->getObjectID();

            $sql = "UPDATE  " . static::getBaseClass()::getDatabaseTableName() . "
                SET     " . $updateSQL . "
                WHERE   " . static::getBaseClass()::getDatabaseTableIndexName() . " = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($statementParameters);

            $object = new (static::getBaseClass())($this->object->getObjectID());
        } else {
            $object = $this->object;
        }

        $this->afterUpdate($object);

        return $object;
    }

    /**
     * Creates a new object, returns null if the row already exists.
     *
     * @return ?TDatabaseObject
     */
    final public function createOrIgnore(): ?DatabaseObject
    {
        if ($this->object !== null) {
            throw new \BadMethodCallException("createOrIgnore() can only be used with forCreate().");
        }

        try {
            return $this->create();
        } catch (DatabaseQueryExecutionException $e) {
            // Error code 23000 = duplicate key
            if (\intval($e->getCode()) === 23000 && $e->getDriverCode() === '1062') {
                return null;
            }

            throw $e;
        }
    }

    /**
     * Deletes the given database object.
     *
     * @param TDatabaseObject $object
     */
    final public static function delete(DatabaseObject $object): void
    {
        static::deleteAll([$object->getObjectID()]);
    }

    /**
     * Deletes the rows identified by the given primary keys in batches inside
     * a single transaction.
     *
     * @param non-empty-list<int>|non-empty-list<string> $objectIDs
     */
    final public static function deleteAll(array $objectIDs): void
    {
        static::beforeDeleteAll($objectIDs);

        $itemsPerLoop = 1000;
        $loopCount = \ceil(\count($objectIDs) / $itemsPerLoop);

        WCF::getDB()->beginTransaction();
        $committed = false;
        try {
            for ($i = 0; $i < $loopCount; $i++) {
                $batchObjectIDs = \array_slice($objectIDs, $i * $itemsPerLoop, $itemsPerLoop);

                $conditionBuilder = new PreparedStatementConditionBuilder();
                $conditionBuilder->add(static::getBaseClass()::getDatabaseTableIndexName() . ' IN (?)', [$batchObjectIDs]);

                $sql = "DELETE FROM " . static::getBaseClass()::getDatabaseTableName() . "
                        " . $conditionBuilder;
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute($conditionBuilder->getParameters());
            }
            WCF::getDB()->commitTransaction();
            $committed = true;
        } finally {
            if (!$committed) {
                WCF::getDB()->rollBackTransaction();
            }
        }
    }

    /**
     * Returns a builder instance for inserting a new row.
     */
    final public static function forCreate(): static
    {
        return new (static::class)();
    }

    /**
     * Returns a builder instance for updating an existing database object.
     *
     * @param TDatabaseObject $object
     */
    final public static function forUpdate(DatabaseObject $object): static
    {
        return new static($object);
    }

    /**
     * Resolves the database object class associated with this builder by
     * stripping the `Builder` suffix from the current class name.
     *
     * @return class-string<DatabaseObject>
     */
    final public static function getBaseClass(): string
    {
        if (!\str_ends_with(static::class, 'Builder')) {
            throw new \LogicException("Builder class '" . static::class . "' must end with the 'Builder' suffix.");
        }

        $className = \mb_substr(static::class, 0, -7);
        if (!\class_exists($className)) {
            throw new ClassNotFoundException($className);
        }

        if (!\is_subclass_of($className, DatabaseObject::class)) {
            throw new ImplementationException($className, DatabaseObject::class);
        }

        return $className;
    }

    /**
     * Sets a custom property value that is written alongside the regular
     * properties when the object is persisted.
     */
    final public function setCustomProperty(string $name, string|int|float|null $value): static
    {
        $this->customProperties[$name] = $value;

        return $this;
    }

    /**
     * This method is called after the properties have been validated.
     * It can be overriden to handle additional tasks that are not handled by the default implementation.
     * You SHOULD NOT modify any properties in this method.
     */
    protected function afterValidateCreate(): void
    {
        // does nothing
    }

    /**
     * This method is called after the creation of a new object.
     * It can be overriden to handle additional tasks that are not handled by the default implementation.
     *
     * @param TDatabaseObject $object
     */
    protected function afterCreate(DatabaseObject $object): void
    {
        // does nothing
    }

    /**
     * This method is called after an update.
     * It can be overriden to handle additional tasks that are not handled by the default implementation.
     *
     * @param TDatabaseObject $object
     */
    protected function afterUpdate(DatabaseObject $object): void
    {
        // does nothing
    }

    /**
     * This method is called before the deletion of objects.
     * It can be overriden to handle additional tasks that are not handled by the default implementation.
     *
     * @param non-empty-list<int>|non-empty-list<string> $objectIDs
     */
    protected static function beforeDeleteAll(array $objectIDs): void
    {
        // does nothing
    }

    /**
     * Sets the ID of the object that is being created.
     *
     * This method should only be used in cases where the ID needs to be set
     * explicitly, for example when importing existing records from another
     * installation, where the ID should be preserved if possible.
     *
     * @throws \BadMethodCallException if an existing object is being updated
     */
    final public function setID(int|string $id): static
    {
        if ($this->object !== null) {
            throw new \BadMethodCallException('The ID cannot be set when updating an existing object.');
        }

        $this->properties[static::getBaseClass()::getDatabaseTableIndexName()] = $id;

        return $this;
    }

    final public function isUpdate(): bool
    {
        return $this->object !== null;
    }

    /**
     * @return TDatabaseObject
     */
    final public function getObject(): DatabaseObject
    {
        if ($this->object === null) {
            throw new \BadMethodCallException('The object can only be retrieved for builders created with `forUpdate()`.');
        }

        return $this->object;
    }
}
