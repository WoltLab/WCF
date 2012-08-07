<?php
namespace wcf\data;
use wcf\system\WCF;

/**
 * Basic implementation for object editors following the decorator pattern.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data
 * @category 	Community Framework
 */
abstract class DatabaseObjectEditor extends DatabaseObjectDecorator implements IEditableObject {
	/**
	 * @see wcf\data\IEditableObject::create()
	 */
	public static function create(array $parameters = array()) {
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
		$sql = "INSERT INTO	".static::getDatabaseTableName()."
					(".$keys.")
			VALUES		(".$values.")";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($statementParameters);
		
		// return new object
		if (static::getDatabaseTableIndexIsIdentity()) {
			$id = WCF::getDB()->getInsertID(static::getDatabaseTableName(), static::getDatabaseTableIndexName());
		}
		else {
			$id = $parameters[static::getDatabaseTableIndexName()];
		}
		return new static::$baseClass($id);
	}
	
	/**
	 * @see wcf\data\IEditableObject::update()
	 */
	public function update(array $parameters = array()) {
		if (!count($parameters)) return;
		
		// check whether any value changed or not
		$update = false;
		foreach ($parameters as $name => $value) {
			if ($this->__get($name) != $value) {
				$update = true;
				break;
			}
		}
		
		// there is no new data - break to avoid senseless sql queries
		if (!$update) return;
		
		$updateSQL = '';
		$statementParameters = array();
		foreach ($parameters as $key => $value) {
			if (!empty($updateSQL)) $updateSQL .= ', ';
			$updateSQL .= $key . ' = ?';
			$statementParameters[] = $value;
		}
		$statementParameters[] = $this->__get(static::getDatabaseTableIndexName());
		
		$sql = "UPDATE	".static::getDatabaseTableName()."
			SET	".$updateSQL."
			WHERE	".static::getDatabaseTableIndexName()." = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($statementParameters);
	}
	
	/**
	 * @see wcf\data\IEditableObject::updateCounters()
	 */
	public function updateCounters(array $counters = array()) {
		if (!count($counters)) return;
		
		$updateSQL = '';
		$statementParameters = array();
		foreach ($counters as $key => $value) {
			if (!empty($updateSQL)) $updateSQL .= ', ';
			$updateSQL .= $key . ' = ' . $key . ' + ?';
			$statementParameters[] = $value;
		}
		$statementParameters[] = $this->__get(static::getDatabaseTableIndexName());
		
		$sql = "UPDATE	".static::getDatabaseTableName()."
			SET	".$updateSQL."
			WHERE	".static::getDatabaseTableIndexName()." = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($statementParameters);
	}
	
	/**
	 * @see wcf\data\IEditableObject::delete()
	 */
	public function delete() {
		static::deleteAll(array($this->__get(static::getDatabaseTableIndexName())));
	}
	
	/**
	 * @see wcf\data\IEditableObject::deleteAll()
	 */
	public static function deleteAll(array $objectIDs = array()) {
		$sql = "DELETE FROM	".static::getDatabaseTableName()."
			WHERE		".static::getDatabaseTableIndexName()." = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		$affectedCount = 0;
		WCF::getDB()->beginTransaction();
		foreach ($objectIDs as $objectID) {
			$statement->executeUnbuffered(array($objectID));
			$affectedCount += $statement->getAffectedRows();
		}
		WCF::getDB()->commitTransaction();
		
		return $affectedCount;
	}
}
