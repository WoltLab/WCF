<?php
namespace wcf\system\search\mysql;
use wcf\data\object\type\ObjectType;
use wcf\system\search\AbstractSearchIndexManager;
use wcf\system\search\SearchIndexManager;
use wcf\system\WCF;

/**
 * Search engine using MySQL's FULLTEXT index.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Search
 */
class MysqlSearchIndexManager extends AbstractSearchIndexManager {
	/**
	 * @inheritDoc
	 */
	public function add($objectType, $objectID, $message, $subject, $time, $userID, $username, $languageID = null, $metaData = '') {
		if ($languageID === null) $languageID = 0;
		
		// save new entry
		$sql = "REPLACE INTO	" . SearchIndexManager::getTableName($objectType) . "
					(objectID, subject, message, time, userID, username, languageID, metaData)
			VALUES		(?, ?, ?, ?, ?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$objectID, $subject, $message, $time, $userID, $username, $languageID, $metaData]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function update($objectType, $objectID, $message, $subject, $time, $userID, $username, $languageID = null, $metaData = '') {
		// delete existing entry
		$this->delete($objectType, [$objectID]);
		
		// save new entry
		$this->add($objectType, $objectID, $message, $subject, $time, $userID, $username, $languageID, $metaData);
	}
	
	/**
	 * @inheritDoc
	 */
	public function delete($objectType, array $objectIDs) {
		$sql = "DELETE FROM	" . SearchIndexManager::getTableName($objectType) . "
			WHERE		objectID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		WCF::getDB()->beginTransaction();
		foreach ($objectIDs as $objectID) {
			$statement->execute([$objectID]);
		}
		WCF::getDB()->commitTransaction();
	}
	
	/**
	 * @inheritDoc
	 */
	public function reset($objectType) {
		$sql = "TRUNCATE TABLE " . SearchIndexManager::getTableName($objectType);
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
	}
	
	/**
	 * @inheritDoc
	 */
	protected function createSearchIndex(ObjectType $objectType) {
		$tableName = SearchIndexManager::getTableName($objectType);
		
		// check if table already exists
		$sql = "SELECT	COUNT(*)
			FROM	wcf".WCF_N."_package_installation_sql_log
			WHERE	sqlTable = ?";
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
			['name' => 'username', 'data' => ['default' => '', 'length' => 255,'notNull' => true, 'type' => 'varchar']],
			['name' => 'languageID', 'data' => ['default' => 0, 'length' => 10, 'notNull' => true, 'type' => 'int']]
		];
		
		$indices = [
			['name' => 'objectAndLanguage', 'data' => ['columns' => 'objectID, languageID', 'type' => 'UNIQUE']],
			['name' => 'fulltextIndex', 'data' => ['columns' => 'subject, message, metaData', 'type' => 'FULLTEXT']],
			['name' => 'fulltextIndexSubjectOnly', 'data' => ['columns' => 'subject', 'type' => 'FULLTEXT']],
			['name' => 'language', 'data' => ['columns' => 'languageID', 'type' => 'KEY']],
			['name' => 'user', 'data' => ['columns' => 'userID, time', 'type'=> 'KEY']]
		];
		
		WCF::getDB()->getEditor()->createTable($tableName, $columns, $indices);
		
		// add comment
		$sql = "ALTER TABLE	".$tableName."
			COMMENT		= 'Search index for ".$objectType->objectType."'";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		
		// log table
		$sql = "INSERT INTO	wcf".WCF_N."_package_installation_sql_log
					(packageID, sqlTable)
			VALUES		(?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$objectType->packageID,
			$tableName
		]);
		
		return true;
	}
}
