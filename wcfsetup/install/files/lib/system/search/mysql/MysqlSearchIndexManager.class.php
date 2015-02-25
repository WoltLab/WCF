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
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.search
 * @category	Community Framework
 */
class MysqlSearchIndexManager extends AbstractSearchIndexManager {
	/**
	 * @see	\wcf\system\search\ISearchIndexManager::add()
	 */
	public function add($objectType, $objectID, $message, $subject, $time, $userID, $username, $languageID = null, $metaData = '') {
		if ($languageID === null) $languageID = 0;
		
		// save new entry
		$sql = "REPLACE INTO	" . SearchIndexManager::getTableName($objectType) . "
					(objectID, subject, message, time, userID, username, languageID, metaData)
			VALUES		(?, ?, ?, ?, ?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($objectID, $subject, $message, $time, $userID, $username, $languageID, $metaData));
	}
	
	/**
	 * @see	\wcf\system\search\ISearchIndexManager::update()
	 */
	public function update($objectType, $objectID, $message, $subject, $time, $userID, $username, $languageID = null, $metaData = '') {
		// delete existing entry
		$this->delete($objectType, array($objectID));
		
		// save new entry
		$this->add($objectType, $objectID, $message, $subject, $time, $userID, $username, $languageID, $metaData);
	}
	
	/**
	 * @see	\wcf\system\search\ISearchIndexManager::delete()
	 */
	public function delete($objectType, array $objectIDs) {
		$sql = "DELETE FROM	" . SearchIndexManager::getTableName($objectType) . "
			WHERE		objectID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		WCF::getDB()->beginTransaction();
		foreach ($objectIDs as $objectID) {
			$statement->execute(array($objectID));
		}
		WCF::getDB()->commitTransaction();
	}
	
	/**
	 * @see	\wcf\system\search\ISearchIndexManager::reset()
	 */
	public function reset($objectType) {
		$sql = "TRUNCATE TABLE " . SearchIndexManager::getTableName($objectType);
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
	}
	
	/**
	 * @see	\wcf\system\search\AbstractSearchIndexManager::createSearchIndex()
	 */
	protected function createSearchIndex(ObjectType $objectType) {
		$tableName = SearchIndexManager::getTableName($objectType);
		
		// check if table already exists
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_package_installation_sql_log
			WHERE	sqlTable = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($tableName));
		$row = $statement->fetchArray();
		if ($row['count']) {
			// table already exists
			return false;
		}
		
		$columns = array(
			array('name' => 'objectID', 'data' => array('length' => 10, 'notNull' => true, 'type' => 'int')),
			array('name' => 'subject', 'data' => array('default' => '', 'length' => 255, 'notNull' => true, 'type' => 'varchar')),
			array('name' => 'message', 'data' => array('type' => 'mediumtext')),
			array('name' => 'metaData', 'data' => array('type' => 'mediumtext')),
			array('name' => 'time', 'data' => array('default' => 0, 'length' => 10, 'notNull' => true, 'type' => 'int')),
			array('name' => 'userID', 'data' => array('default' => '', 'length' => 10, 'type' => 'int')),
			array('name' => 'username', 'data' => array('default' => '', 'length' => 255,'notNull' => true, 'type' => 'varchar')),
			array('name' => 'languageID', 'data' => array('default' => 0, 'length' => 10, 'notNull' => true, 'type' => 'int'))
		);
		
		$indices = array(
			array('name' => 'objectAndLanguage', 'data' => array('columns' => 'objectID, languageID', 'type' => 'UNIQUE')),
			array('name' => 'fulltextIndex', 'data' => array('columns' => 'subject, message, metaData', 'type' => 'FULLTEXT')),
			array('name' => 'fulltextIndexSubjectOnly', 'data' => array('columns' => 'subject', 'type' => 'FULLTEXT')),
			array('name' => 'language', 'data' => array('columns' => 'languageID', 'type' => 'KEY')),
			array('name' => 'user', 'data' => array('columns' => 'userID, time', 'type'=> 'KEY'))
		);
		
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
		$statement->execute(array(
			$objectType->packageID,
			$tableName
		));
		
		return true;
	}
}
