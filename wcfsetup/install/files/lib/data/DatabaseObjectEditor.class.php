<?php
namespace wcf\data;
use wcf\system\WCF;

/**
 * Basic implementation for object editors following the decorator pattern.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data
 */
abstract class DatabaseObjectEditor extends DatabaseObjectDecorator implements IEditableObject {
	/**
	 * @inheritDoc
	 */
	public static function create(array $parameters = []) {
		$keys = $values = '';
		$statementParameters = [];
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
	 * @inheritDoc
	 */
	public function update(array $parameters = []) {
		if (empty($parameters)) return;
		
		$updateSQL = '';
		$statementParameters = [];
		foreach ($parameters as $key => $value) {
			if (!empty($updateSQL)) $updateSQL .= ', ';
			$updateSQL .= $key . ' = ?';
			$statementParameters[] = $value;
		}
		$statementParameters[] = $this->getObjectID();
		
		$sql = "UPDATE	".static::getDatabaseTableName()."
			SET	".$updateSQL."
			WHERE	".static::getDatabaseTableIndexName()." = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($statementParameters);
	}
	
	/**
	 * @inheritDoc
	 */
	public function updateCounters(array $counters = []) {
		if (empty($counters)) return;
		
		$updateSQL = '';
		$statementParameters = [];
		foreach ($counters as $key => $value) {
			if (!empty($updateSQL)) $updateSQL .= ', ';
			$updateSQL .= $key . ' = ' . $key . ' + ?';
			$statementParameters[] = $value;
		}
		$statementParameters[] = $this->getObjectID();
		
		$sql = "UPDATE	".static::getDatabaseTableName()."
			SET	".$updateSQL."
			WHERE	".static::getDatabaseTableIndexName()." = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($statementParameters);
	}
	
	/**
	 * @inheritDoc
	 */
	public function delete() {
		static::deleteAll([$this->getObjectID()]);
	}
	
	/**
	 * @inheritDoc
	 */
	public static function deleteAll(array $objectIDs = []) {
		$affectedCount = 0;
		
		// instead of executing one query per object id, execute queries
		// for batches of up to 1000 object ids at once
		$itemsPerLoop = 1000;
		$loopCount = ceil(count($objectIDs) / $itemsPerLoop);
		
		WCF::getDB()->beginTransaction();
		for ($i = 0; $i < $loopCount; $i++) {
			$batchObjectIDs = array_slice($objectIDs, $i * $itemsPerLoop, $itemsPerLoop);
			
			$sql = "DELETE FROM	" . static::getDatabaseTableName() . "
				WHERE		" . static::getDatabaseTableIndexName() . " IN (?" . str_repeat(', ?', count($batchObjectIDs) - 1) . ")";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($batchObjectIDs);
			$affectedCount += $statement->getAffectedRows();
		}
		WCF::getDB()->commitTransaction();
		
		return $affectedCount;
	}
}
