<?php

namespace wcf\system\version;

use wcf\data\DatabaseObject;
use wcf\data\IVersionTrackerObject;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\object\type\ObjectTypeList;
use wcf\system\application\ApplicationHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Represents objects that support some of their properties to be saved.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class VersionTracker extends SingletonFactory
{
    /**
     * list of available object types
     * @var ObjectType[]
     */
    protected $availableObjectTypes = [];

    #[\Override]
    protected function init()
    {
        // get available object types
        $this->availableObjectTypes = ObjectTypeCache::getInstance()
            ->getObjectTypes('com.woltlab.wcf.versionTracker.objectType');
    }

    /**
     * Adds a new entry to the version history.
     *
     * @return void
     */
    public function add(string $objectTypeName, IVersionTrackerObject $object)
    {
        $objectType = $this->getObjectType($objectTypeName);

        /** @var IVersionTrackerProvider<DatabaseObject> $processor */
        $processor = $objectType->getProcessor();
        $data = $processor->getTrackedData($object);

        $sql = "INSERT INTO " . $this->getTableName($objectType) . "_version
                            (objectID, userID, username, time, data)
                VALUES      (?, ?, ?, ?, ?)";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $object->getObjectID(),
            WCF::getUser()->userID,
            WCF::getUser()->username,
            TIME_NOW,
            \serialize($data),
        ]);
    }

    /**
     * Returns the number of stored versions for provided object type and object id.
     *
     * @return      int         number of stored versions
     */
    public function countVersions(string $objectTypeName, int $objectID)
    {
        $objectType = $this->getObjectType($objectTypeName);

        $sql = "SELECT  COUNT(*) as count
                FROM    " . $this->getTableName($objectType) . "_version
                WHERE   objectID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$objectID]);

        return $statement->fetchSingleColumn();
    }

    /**
     * Returns the last stored version.
     *
     * @return ?VersionTrackerEntry
     */
    public function getLastVersion(string $objectTypeName, int $objectID)
    {
        $objectType = $this->getObjectType($objectTypeName);

        $sql = "SELECT      *, '' as data
                FROM        " . $this->getTableName($objectType) . "_version
                WHERE       objectID = ?
                ORDER BY    versionID DESC";
        $statement = WCF::getDB()->prepare($sql, 1);
        $statement->execute([$objectID]);
        $row = $statement->fetchSingleRow();
        if ($row === false) {
            return null;
        }

        return new VersionTrackerEntry(null, $row);
    }

    /**
     * Returns the list of stored versions.
     *
     * @return      VersionTrackerEntry[]
     */
    public function getVersions(string $objectTypeName, int $objectID)
    {
        $objectType = $this->getObjectType($objectTypeName);

        $sql = "SELECT      *, '' as data
                FROM        " . $this->getTableName($objectType) . "_version
                WHERE       objectID = ?
                ORDER BY    versionID DESC";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$objectID]);
        $versions = [];
        while ($row = $statement->fetchArray()) {
            $versions[] = new VersionTrackerEntry(null, $row);
        }

        return $versions;
    }

    /**
     * Returns a version including the contents of the data column.
     *
     * @return ?VersionTrackerEntry
     */
    public function getVersion(string $objectTypeName, int $versionID)
    {
        $objectType = $this->getObjectType($objectTypeName);

        $sql = "SELECT  *
                FROM    " . $this->getTableName($objectType) . "_version
                WHERE   versionID = ?";
        $statement = WCF::getDB()->prepare($sql, 1);
        $statement->execute([$versionID]);
        $row = $statement->fetchSingleRow();
        if ($row === false) {
            return null;
        }

        return new VersionTrackerEntry(null, $row);
    }

    /**
     * Creates the database tables to store each version.
     *
     * @return void
     */
    public function createStorageTables()
    {
        // get definition id
        $sql = "SELECT  definitionID
                FROM    wcf1_object_type_definition
                WHERE   definitionName = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute(['com.woltlab.wcf.versionTracker.objectType']);
        $row = $statement->fetchArray();

        $objectTypeList = new ObjectTypeList();
        $objectTypeList->getConditionBuilder()->add("object_type.definitionID = ?", [$row['definitionID']]);
        $objectTypeList->readObjects();

        foreach ($objectTypeList as $objectType) {
            $this->createStorageTable($objectType);
        }
    }

    /**
     * Retrieves the object type object by its name.
     *
     * @return      ObjectType      target object
     * @throws      \InvalidArgumentException
     */
    public function getObjectType(string $name)
    {
        foreach ($this->availableObjectTypes as $objectType) {
            if ($objectType->objectType === $name) {
                return $objectType;
            }
        }

        throw new \InvalidArgumentException(
            "Unknown object type '" . $name . "' for definition 'com.woltlab.wcf.versionTracker.objectType'."
        );
    }

    /**
     * Resets the entire history for provided object id.
     *
     * @return void
     */
    public function reset(string $objectTypeName, int $objectID)
    {
        $objectType = $this->getObjectType($objectTypeName);

        $sql = "DELETE FROM " . $this->getTableName($objectType) . "_version
                WHERE       objectID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$objectID]);
    }

    /**
     * Creates a database table for an object type unless it exists already.
     *
     * @param ObjectType $objectType target object type
     * @return      bool         false if table already exists
     */
    protected function createStorageTable(ObjectType $objectType)
    {
        $baseTableName = $this->getTableName($objectType);
        $tableName = $baseTableName . '_version';

        // check if table already exists
        $sql = "SELECT  COUNT(*)
                FROM    wcf1_package_installation_sql_log
                WHERE   sqlTable = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$tableName]);

        if ($statement->fetchSingleColumn()) {
            // table already exists
            return false;
        }

        $columns = [
            [
                'name' => 'versionID',
                'data' => [
                    'length' => 10,
                    'notNull' => true,
                    'type' => 'int',
                    'key' => 'PRIMARY',
                    'autoIncrement' => true,
                ],
            ],
            ['name' => 'objectID', 'data' => ['length' => 10, 'notNull' => true, 'type' => 'int']],
            ['name' => 'userID', 'data' => ['length' => 10, 'type' => 'int']],
            ['name' => 'username', 'data' => ['length' => 100, 'notNull' => true, 'type' => 'varchar']],
            ['name' => 'time', 'data' => ['length' => 10, 'notNull' => true, 'type' => 'int']],
            ['name' => 'data', 'data' => ['type' => 'longblob']],
        ];

        WCF::getDB()->getEditor()->createTable($tableName, $columns);
        WCF::getDB()->getEditor()->addForeignKey(
            $tableName,
            \md5($tableName . '_objectID') . '_fk',
            [
                'columns' => 'objectID',
                'referencedTable' => $baseTableName,
                'referencedColumns' => $objectType->tablePrimaryKey,
                'ON DELETE' => 'CASCADE',
            ]
        );
        WCF::getDB()->getEditor()->addForeignKey(
            $tableName,
            \md5($tableName . '_userID') . '_fk',
            [
                'columns' => 'userID',
                'referencedTable' => 'wcf1_user',
                'referencedColumns' => 'userID',
                'ON DELETE' => 'SET NULL',
            ]
        );

        // add comment
        $sql = "ALTER TABLE " . $tableName . "
                COMMENT     = 'Version tracking for " . $objectType->objectType . "'";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();

        // log table
        $sql = "INSERT INTO wcf1_package_installation_sql_log
                            (packageID, sqlTable)
                VALUES      (?, ?)";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $objectType->packageID,
            $tableName,
        ]);

        return true;
    }

    /**
     * Retrieves the database table name.
     *
     * @param ObjectType $objectType target object type
     * @return      string          database table name
     */
    protected function getTableName(ObjectType $objectType)
    {
        return ApplicationHandler::insertRealDatabaseTableNames($objectType->tableName);
    }
}
