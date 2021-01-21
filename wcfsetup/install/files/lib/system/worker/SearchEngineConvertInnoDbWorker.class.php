<?php

namespace wcf\system\worker;

use wcf\data\object\type\ObjectTypeCache;
use wcf\system\request\LinkHandler;
use wcf\system\search\SearchIndexManager;
use wcf\system\WCF;

/**
 * Worker implementation for search table engine conversion.
 *
 * @author  Tim Duesterhus, Alexander Ebert
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Worker
 * @since       5.4
 */
class SearchEngineConvertInnoDbWorker extends AbstractRebuildDataWorker
{
    /**
     * @inheritDoc
     */
    protected $limit = 1;

    /**
     * @inheritDoc
     */
    public function initObjectList()
    {
        // This rebuild worker has no database object list and
        // therefore, we do nothing in this method and overwrite
        // the parent method so no exception is thrown.
    }

    /**
     * @inheritDoc
     */
    public function countObjects()
    {
        if ($this->count === null) {
            $this->count = \count($this->getTables());
        }
    }

    /**
     * @inheritDoc
     */
    public function getProceedURL()
    {
        return LinkHandler::getInstance()->getLink('RebuildData');
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        WCF::getSession()->checkPermissions(['admin.management.canRebuildData']);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $tables = $this->getTables();

        $sql = "SELECT          LOWER(ENGINE)
			FROM            INFORMATION_SCHEMA.TABLES
			WHERE           TABLE_NAME = ?
				AND     TABLE_SCHEMA = ?";
        $checkStatement = WCF::getDB()->prepareStatement($sql);

        $convertTables = \array_slice($tables, $this->limit * $this->loopCount, $this->limit);
        foreach ($convertTables as $table) {
            $checkStatement->execute([
                $table,
                WCF::getDB()->getDatabaseName(),
            ]);
            if ($checkStatement->fetchSingleColumn() !== 'innodb') {
                $sql = "ALTER TABLE " . $table . " ENGINE = InnoDB";
                $statement = WCF::getDB()->prepareStatement($sql);
                $statement->execute();
            }
        }
    }

    /**
     * Returns the list of known database tables.
     *
     * @return      string[]
     */
    protected function getTables()
    {
        $objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.searchableObjectType');
        $tableNames = [];
        foreach ($objectTypes as $objectType) {
            $tableNames[] = SearchIndexManager::getTableName($objectType->objectType);
        }

        return $tableNames;
    }
}
