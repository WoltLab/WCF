<?php
namespace wcf\data;
use wcf\system\WCF;

/**
 * Abstract class for all versionable editor classes.
 * 
 * @deprecated	2.1 - will be removed with WCF 2.2
 * 
 * @author	Jeffrey Reichardt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data
 * @category	Community Framework
 */
abstract class VersionableDatabaseObjectEditor extends DatabaseObjectEditor {
	/**
	 * @see	\wcf\data\IEditableObject::create()
	 */
	public static function createRevision(array $parameters = array()) {
		$keys = $values = '';
		$statementParameters = array();
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
		$sql = "INSERT INTO	".static::getDatabaseVersionTableName()." (".$keys.")
				VALUES (".$values.")";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($statementParameters);
		
		// return new object
		$id = WCF::getDB()->getInsertID(static::getDatabaseVersionTableName(), static::getDatabaseVersionTableIndexName());
		
		return new static::$baseClass($id);
	}
	
	/**
	 * @see	\wcf\data\IEditableObject::delete()
	 */
	public function deleteRevision(array $objectIDs = array()) {
		static::deleteAll(array($this->__get(static::getDatabaseVersionTableIndexName())));
	}
	
	/**
	 * @see	\wcf\data\IEditableObject::deleteAll()
	 */
	public static function deleteAll(array $objectIDs = array()) {
		$affectedCount = static::deleteAll($objectIDs);
		
		// delete versions
		$sql = "DELETE FROM	".static::getDatabaseVersionTableName()."
				WHERE ".static::getDatabaseTableIndexName()." = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		WCF::getDB()->beginTransaction();
		foreach ($objectIDs as $objectID) {
			$statement->execute(array($objectID));
		}
		WCF::getDB()->commitTransaction();
		
		return $affectedCount;
	}
	
	/**
	 * @see	\wcf\data\VersionableDatabaseObject::getDatabaseVersionTableName()
	 */
	public static function getDatabaseVersionTableName() {
		return call_user_func(array(static::$baseClass, 'getDatabaseVersionTableName'));
	}
	
	/**
	 * @see	\wcf\data\VersionableDatabaseObject::getDatabaseVersionTableIndexName()
	 */
	public static function getDatabaseVersionTableIndexName() {
		return call_user_func(array(static::$baseClass, 'getDatabaseVersionTableIndexName'));
	}
}
