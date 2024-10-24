<?php

namespace wcf\system\importer;

use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\CacheHandler;
use wcf\system\database\exception\DatabaseException;
use wcf\system\exception\SystemException;
use wcf\system\IAJAXInvokeAction;
use wcf\system\SingletonFactory;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Handles data import.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ImportHandler extends SingletonFactory implements IAJAXInvokeAction
{
    /**
     * id map cache
     * @var array
     */
    protected $idMappingCache = [];

    /**
     * list of available importers
     * @var array
     */
    protected $objectTypes = [];

    /**
     * list of available importer processors
     * @var array
     */
    protected $importers = [];

    /**
     * user merge mode
     * @var int
     */
    protected $userMergeMode = 2;

    /**
     * import hash
     * @var string
     */
    protected $importHash = '';

    /**
     * list of methods allowed for remote invoke
     * @var string[]
     */
    public static $allowInvoke = ['resetMapping'];

    /**
     * @inheritDoc
     */
    protected function init()
    {
        $this->objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.importer');
    }

    /**
     * Returns a data importer.
     *
     * @param string $type
     * @return  IImporter
     * @throws  SystemException
     */
    public function getImporter($type)
    {
        if (!isset($this->importers[$type])) {
            if (!isset($this->objectTypes[$type])) {
                throw new SystemException("unknown importer '" . $type . "'");
            }

            $this->importers[$type] = $this->objectTypes[$type]->getProcessor();
        }

        return $this->importers[$type];
    }

    /**
     * Returns a new id from id mapping.
     *
     * @param string $type
     * @param mixed $oldID
     * @return  int|null
     */
    public function getNewID($type, $oldID)
    {
        if (!$oldID) {
            return null;
        }
        $objectTypeID = $this->objectTypes[$type]->objectTypeID;

        if (
            !isset($this->idMappingCache[$objectTypeID])
            || !\array_key_exists($oldID, $this->idMappingCache[$objectTypeID])
        ) {
            $this->idMappingCache[$objectTypeID][$oldID] = null;
            $importer = $this->getImporter($type);
            $tableName = $indexName = '';
            if ($importer->getClassName()) {
                $tableName = \call_user_func([$importer->getClassName(), 'getDatabaseTableName']);
                $indexName = \call_user_func([$importer->getClassName(), 'getDatabaseTableIndexName']);
            }

            $sql = "SELECT  import_mapping.newID
                    FROM    wcf1_import_mapping import_mapping
                    " . ($tableName ? "
                        LEFT JOIN   " . $tableName . " object_table
                        ON          object_table." . $indexName . " = import_mapping.newID
                        " : '') . "
                    WHERE   import_mapping.importHash = ?
                        AND import_mapping.objectTypeID = ?
                        AND import_mapping.oldID = ?
                            " . ($tableName ? "AND object_table." . $indexName . " IS NOT NULL" : '');
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$this->importHash, $objectTypeID, $oldID]);
            $row = $statement->fetchArray();
            if ($row !== false) {
                $this->idMappingCache[$objectTypeID][$oldID] = $row['newID'];
            }
        }

        return $this->idMappingCache[$objectTypeID][$oldID];
    }

    /**
     * Saves an id mapping.
     *
     * @param string $type
     * @param int $oldID
     * @param int $newID
     */
    public function saveNewID($type, $oldID, $newID)
    {
        static $statement = null;

        $objectTypeID = $this->objectTypes[$type]->objectTypeID;

        if ($statement === null) {
            $sql = "INSERT IGNORE INTO  wcf1_import_mapping
                                        (importHash, objectTypeID, oldID, newID)
                    VALUES              (?, ?, ?, ?)";
            $statement = WCF::getDB()->prepare($sql);
        }

        try {
            $statement->execute([$this->importHash, $objectTypeID, $oldID, $newID]);
        } catch (DatabaseException $e) {
            // Re-prepare the statement once.
            $sql = "INSERT IGNORE INTO  wcf1_import_mapping
                                        (importHash, objectTypeID, oldID, newID)
                    VALUES              (?, ?, ?, ?)";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$this->importHash, $objectTypeID, $oldID, $newID]);
        }

        unset($this->idMappingCache[$objectTypeID][$oldID]);
    }

    /**
     * Validates accessibility of resetMapping().
     */
    public function validateResetMapping()
    {
        WCF::getSession()->checkPermissions(['admin.management.canImportData']);

        // reset caches
        CacheHandler::getInstance()->flushAll();
        UserStorageHandler::getInstance()->clear();
    }

    /**
     * Resets the mapping.
     */
    public function resetMapping()
    {
        $sql = "DELETE FROM wcf1_import_mapping";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();

        $this->idMappingCache = [];
    }

    /**
     * Sets the user merge mode.
     *
     * @param int $mode
     */
    public function setUserMergeMode($mode)
    {
        $this->userMergeMode = $mode;
    }

    /**
     * Returns the user merge mode.
     *
     * @return  int
     */
    public function getUserMergeMode()
    {
        return $this->userMergeMode;
    }

    /**
     * Sets the import hash.
     *
     * @param string $hash
     */
    public function setImportHash($hash)
    {
        $this->importHash = $hash;
    }
}
