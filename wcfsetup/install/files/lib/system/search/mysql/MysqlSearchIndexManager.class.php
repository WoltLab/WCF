<?php

namespace wcf\system\search\mysql;

use wcf\data\object\type\ObjectType;
use wcf\system\database\exception\DatabaseQueryExecutionException;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\search\AbstractSearchIndexManager;
use wcf\system\search\SearchIndexManager;
use wcf\system\WCF;

/**
 * Search engine using MySQL's FULLTEXT index.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class MysqlSearchIndexManager extends AbstractSearchIndexManager
{
    /**
     * @inheritDoc
     */
    public function set(
        $objectType,
        $objectID,
        $message,
        $subject,
        $time,
        $userID,
        $username,
        $languageID = null,
        $metaData = ''
    ) {
        if ($languageID === null) {
            $languageID = 0;
        }

        // save new entry
        $sql = "REPLACE INTO    " . SearchIndexManager::getTableName($objectType) . "
                                (objectID, subject, message, time, userID, username, languageID, metaData)
                VALUES          (?, ?, ?, ?, ?, ?, ?, ?)";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$objectID, $subject, $message, $time, $userID, $username, $languageID, $metaData]);
    }

    /**
     * @inheritDoc
     */
    public function delete($objectType, array $objectIDs)
    {
        $itemsPerLoop = 1000;
        $loopCount = \ceil(\count($objectIDs) / $itemsPerLoop);
        $tableName = SearchIndexManager::getTableName($objectType);

        WCF::getDB()->beginTransaction();
        for ($i = 0; $i < $loopCount; $i++) {
            $batchObjectIDs = \array_slice($objectIDs, $i * $itemsPerLoop, $itemsPerLoop);

            $conditionBuilder = new PreparedStatementConditionBuilder();
            $conditionBuilder->add('objectID  IN (?)', [$batchObjectIDs]);

            $sql = "DELETE FROM " . $tableName . "
                    " . $conditionBuilder;
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute($conditionBuilder->getParameters());
        }
        WCF::getDB()->commitTransaction();
    }

    /**
     * @inheritDoc
     */
    public function reset($objectType)
    {
        $sql = "TRUNCATE TABLE " . SearchIndexManager::getTableName($objectType);
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute();
    }

    /**
     * @inheritDoc
     */
    protected function createSearchIndex(ObjectType $objectType)
    {
        $tableName = SearchIndexManager::getTableName($objectType);

        // check if table already exists
        $sql = "SELECT  COUNT(*)
                FROM    wcf" . WCF_N . "_package_installation_sql_log
                WHERE   sqlTable = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$tableName]);

        if ($statement->fetchSingleColumn()) {
            // table already exists
            return false;
        }

        $columns = [
            ['name' => 'objectID', 'data' => ['length' => 10, 'notNull' => true, 'type' => 'int']],
            ['name' => 'subject', 'data' => ['default' => '', 'length' => 255, 'notNull' => true, 'type' => 'varchar']],
            ['name' => 'message', 'data' => ['type' => 'mediumtext']],
            ['name' => 'metaData', 'data' => ['type' => 'mediumtext']],
            ['name' => 'time', 'data' => ['default' => 0, 'length' => 10, 'notNull' => true, 'type' => 'int']],
            ['name' => 'userID', 'data' => ['default' => '', 'length' => 10, 'type' => 'int']],
            [
                'name' => 'username',
                'data' => ['default' => '', 'length' => 255, 'notNull' => true, 'type' => 'varchar'],
            ],
            ['name' => 'languageID', 'data' => ['default' => 0, 'length' => 10, 'notNull' => true, 'type' => 'int']],
        ];

        $indices = [
            ['name' => 'objectAndLanguage', 'data' => ['columns' => 'objectID, languageID', 'type' => 'UNIQUE']],
            ['name' => 'fulltextIndex', 'data' => ['columns' => 'subject, message, metaData', 'type' => 'FULLTEXT']],
            ['name' => 'fulltextIndexSubjectOnly', 'data' => ['columns' => 'subject', 'type' => 'FULLTEXT']],
            ['name' => 'language', 'data' => ['columns' => 'languageID', 'type' => 'KEY']],
            ['name' => 'user', 'data' => ['columns' => 'userID, time', 'type' => 'KEY']],
        ];

        try {
            WCF::getDB()->getEditor()->createTable($tableName, $columns, $indices);
        } catch (DatabaseQueryExecutionException $e) {
            // SQLSTATE[42S01]: Base table or view already exists: 1050 Table '%s' already exists
            if ($e->getCode() !== '42S01') {
                throw $e;
            }
        }

        // add comment
        $sql = "ALTER TABLE " . $tableName . "
                COMMENT     = 'Search index for " . $objectType->objectType . "'";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute();

        // log table
        $sql = "INSERT INTO wcf" . WCF_N . "_package_installation_sql_log
                            (packageID, sqlTable)
                VALUES      (?, ?)";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([
            $objectType->packageID,
            $tableName,
        ]);

        return true;
    }
}
