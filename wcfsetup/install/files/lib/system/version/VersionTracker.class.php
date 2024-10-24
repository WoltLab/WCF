<?php

namespace wcf\system\version;

use wcf\data\DatabaseObject;
use wcf\data\IVersionTrackerObject;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\object\type\ObjectTypeList;
use wcf\system\application\ApplicationHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\IAJAXInvokeAction;
use wcf\system\request\RequestHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents objects that support some of their properties to be saved.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 */
class VersionTracker extends SingletonFactory implements IAJAXInvokeAction
{
    /**
     * list of methods that may be invoked via ajax
     * @var string[]
     */
    public static $allowInvoke = ['revert'];

    /**
     * list of available object types
     * @var ObjectType[]
     */
    protected $availableObjectTypes = [];

    /**
     * version tracker object used for the version revert process
     * @var IVersionTrackerObject
     */
    protected $object;

    /**
     * object type processor object
     * @var IVersionTrackerProvider
     */
    protected $processor;

    /**
     * version tracker entry used for the version revert process
     * @var VersionTrackerEntry
     */
    protected $version;

    /**
     * @inheritDoc
     */
    protected function init()
    {
        // get available object types
        $this->availableObjectTypes = ObjectTypeCache::getInstance()
            ->getObjectTypes('com.woltlab.wcf.versionTracker.objectType');
    }

    /**
     * Adds a new entry to the version history.
     *
     * @param string $objectTypeName object type name
     * @param IVersionTrackerObject $object target object
     */
    public function add($objectTypeName, IVersionTrackerObject $object)
    {
        $objectType = $this->getObjectType($objectTypeName);

        /** @var IVersionTrackerProvider $processor */
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
     * @param string $objectTypeName object type name
     * @param int $objectID target object id
     * @return      int         number of stored versions
     */
    public function countVersions($objectTypeName, $objectID)
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
     * @param string $objectTypeName object type name
     * @param int $objectID target object id
     * @return      VersionTrackerEntry|null|DatabaseObject
     */
    public function getLastVersion($objectTypeName, $objectID)
    {
        $objectType = $this->getObjectType($objectTypeName);

        $sql = "SELECT      *, '' as data
                FROM        " . $this->getTableName($objectType) . "_version
                WHERE       objectID = ?
                ORDER BY    versionID DESC";
        $statement = WCF::getDB()->prepare($sql, 1);
        $statement->execute([$objectID]);

        return $statement->fetchObject(VersionTrackerEntry::class);
    }

    /**
     * Returns the list of stored versions.
     *
     * @param string $objectTypeName object type name
     * @param int $objectID target object id
     * @return      VersionTrackerEntry[]
     */
    public function getVersions($objectTypeName, $objectID)
    {
        $objectType = $this->getObjectType($objectTypeName);

        $sql = "SELECT      *, '' as data
                FROM        " . $this->getTableName($objectType) . "_version
                WHERE       objectID = ?
                ORDER BY    versionID DESC";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$objectID]);
        $versions = [];
        while ($version = $statement->fetchObject(VersionTrackerEntry::class)) {
            $versions[] = $version;
        }

        return $versions;
    }

    /**
     * Returns a version including the contents of the data column.
     *
     * @param string $objectTypeName object type name
     * @param int $versionID version id
     * @return      VersionTrackerEntry|null|DatabaseObject
     */
    public function getVersion($objectTypeName, $versionID)
    {
        $objectType = $this->getObjectType($objectTypeName);

        $sql = "SELECT  *
                FROM    " . $this->getTableName($objectType) . "_version
                WHERE   versionID = ?";
        $statement = WCF::getDB()->prepare($sql, 1);
        $statement->execute([$versionID]);

        return $statement->fetchObject(VersionTrackerEntry::class);
    }

    /**
     * Creates the database tables to store each version.
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
     * @param string $name object type name
     * @return      ObjectType      target object
     * @throws      \InvalidArgumentException
     */
    public function getObjectType($name)
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
     * Validates parameters to revert an object to a previous version.
     *
     * @throws      PermissionDeniedException
     * @throws      UserInputException
     */
    public function validateRevert()
    {
        if (!RequestHandler::getInstance()->isACPRequest()) {
            throw new PermissionDeniedException();
        }

        if (!isset($_POST['parameters'])) {
            throw new UserInputException('parameters');
        }

        $objectTypeName = (isset($_POST['parameters']['objectType'])) ? StringUtil::trim($_POST['parameters']['objectType']) : '';
        $objectID = (isset($_POST['parameters']['objectID'])) ? \intval($_POST['parameters']['objectID']) : 0;
        $versionID = (isset($_POST['parameters']['versionID'])) ? \intval($_POST['parameters']['versionID']) : 0;

        $objectType = $this->getObjectType($objectTypeName);
        /** @var IVersionTrackerProvider $processor */
        $this->processor = $objectType->getProcessor();
        if (!$this->processor->canAccess()) {
            throw new PermissionDeniedException();
        }

        $this->object = $this->processor->getObjectByID($objectID);
        if (!$this->object->getObjectID()) {
            throw new UserInputException('objectID');
        }

        $this->version = $this->getVersion($objectTypeName, $versionID);
        if (!$this->version->versionID) {
            throw new UserInputException('versionID');
        }
    }

    /**
     * Reverts an object to a previous version.
     */
    public function revert()
    {
        $this->processor->revert($this->object, $this->version);
    }

    /**
     * Resets the entire history for provided object id.
     *
     * @param string $objectTypeName object type name
     * @param int $objectID object id
     */
    public function reset($objectTypeName, $objectID)
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
