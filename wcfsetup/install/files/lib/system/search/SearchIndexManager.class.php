<?php
namespace wcf\system\search;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\object\type\ObjectTypeList;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Manages the search index.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.search
 * @category	Community Framework
 */
class SearchIndexManager extends SingletonFactory {
	/**
	 * list of available object types
	 * @var	array
	 */
	protected $availableObjectTypes = array();
	
	/**
	 * @see	\wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		// get available object types
		$this->availableObjectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.searchableObjectType');
	}
	
	/**
	 * Returns the id of the object type with the given name.
	 * 
	 * @param	string		$objectType
	 * @return	integer
	 */
	public function getObjectTypeID($objectType) {
		if (!isset($this->availableObjectTypes[$objectType])) {
			throw new SystemException("unknown object type '".$objectType."'");
		}
		
		return $this->availableObjectTypes[$objectType]->objectTypeID;
	}
	
	/**
	 * Returns the the object type with the given name.
	 *
	 * @param	string		$objectType
	 * @return	\wcf\data\object\type\ObjectType
	 */
	public function getObjectType($objectType) {
		if (!isset($this->availableObjectTypes[$objectType])) {
			throw new SystemException("unknown object type '".$objectType."'");
		}
		
		return $this->availableObjectTypes[$objectType];
	}
	
	/**
	 * Adds a new entry.
	 * 
	 * @param	string		$objectType
	 * @param	integer		$objectID
	 * @param	string		$message
	 * @param	string		$subject
	 * @param	integer		$time
	 * @param	integer		$userID
	 * @param	string		$username
	 * @param	integer		$languageID
	 * @param	string		$metaData
	 */
	public function add($objectType, $objectID, $message, $subject, $time, $userID, $username, $languageID = null, $metaData = '') {
		if ($languageID === null) $languageID = 0;
		
		// save new entry
		$sql = "REPLACE INTO	" . self::getTableName($objectType) . "
					(objectID, subject, message, time, userID, username, languageID, metaData)
			VALUES		(?, ?, ?, ?, ?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($objectID, $subject, $message, $time, $userID, $username, $languageID, $metaData));
	}
	
	/**
	 * Updates the search index.
	 * 
	 * @param	string		$objectType
	 * @param	integer		$objectID
	 * @param	string		$message
	 * @param	string		$subject
	 * @param	integer		$time
	 * @param	integer		$userID
	 * @param	string		$username
	 * @param	integer		$languageID
	 * @param	string		$metaData
	 */
	public function update($objectType, $objectID, $message, $subject, $time, $userID, $username, $languageID = null, $metaData = '') {
		// delete existing entry
		$this->delete($objectType, array($objectID));
		
		// save new entry
		$this->add($objectType, $objectID, $message, $subject, $time, $userID, $username, $languageID, $metaData);
	}
	
	/**
	 * Deletes search index entries.
	 * 
	 * @param	string		$objectType
	 * @param	array<integer>	$objectIDs
	 */
	public function delete($objectType, array $objectIDs) {
		$sql = "DELETE FROM	" . self::getTableName($objectType) . "
			WHERE		objectID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		WCF::getDB()->beginTransaction();
		foreach ($objectIDs as $objectID) {
			$statement->execute(array($objectID));
		}
		WCF::getDB()->commitTransaction();
	}
	
	/**
	 * Resets the search index.
	 * 
	 * @param	string		$objectType
	 */
	public function reset($objectType) {
		$sql = "TRUNCATE TABLE " . self::getTableName($objectType);
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
	}
	
	/**
	 * Creates the search index tables for all registered, searchable object types.
	 */
	public static function createSearchIndexTables() {
		// get definition id
		$sql = "SELECT	definitionID
			FROM	wcf".WCF_N."_object_type_definition
			WHERE	definitionName = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array('com.woltlab.wcf.searchableObjectType'));
		$row = $statement->fetchArray();
		
		$objectTypeList = new ObjectTypeList();
		$objectTypeList->getConditionBuilder()->add("object_type.definitionID = ?", array($row['definitionID']));
		$objectTypeList->readObjects();
		
		foreach ($objectTypeList as $objectType) {
			self::createSearchIndexTable($objectType);
		}
	}
	
	/**
	 * Creates the search index table for given object type. Returns true if the
	 * table was created, otherwise false.
	 * 
	 * @param	\wcf\data\object\type\ObjectType	$objectType
	 * @return	boolean
	 */
	protected static function createSearchIndexTable(ObjectType $objectType) {
		$tableName = self::getTableName($objectType->objectType);
		
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
			COMMENT		= ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(' Search index for ' . $objectType->objectType));
		
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
	
	/**
	 * Returns the database table name for the object type's search index.
	 * 
	 * @param	string		$objectType
	 * @return	string
	 */
	public static function getTableName($objectType) {
		return 'wcf'.WCF_N.'_search_index_'.substr(sha1($objectType), 0, 8);
	}
}
