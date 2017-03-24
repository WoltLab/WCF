<?php
namespace wcf\system\version;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\object\type\ObjectTypeList;
use wcf\data\package\Package;
use wcf\data\package\PackageList;
use wcf\data\IVersionTrackerObject;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Represents objects that support some of their properties to be saved.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Version
 */
class VersionTracker extends SingletonFactory {
	/**
	 * list of available object types
	 * @var ObjectType[]
	 */
	protected $availableObjectTypes = [];
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		// get available object types
		$this->availableObjectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.versionTracker.objectType');
	}
	
	/**
	 * Adds a new entry to the version history.
	 * 
	 * @param       string                  $objectTypeName         object typename
	 * @param       IVersionTrackerObject   $object                 target object
	 */
	public function add($objectTypeName, IVersionTrackerObject $object) {
		$objectType = $this->getObjectType($objectTypeName);
		
		/** @var IVersionTrackerProvider $processor */
		$processor = $objectType->getProcessor();
		$data = $processor->getTrackedData($object);
		
		$sql = "INSERT INTO     ".$this->getTableName($objectType)."_version
					(objectID, data)
			VALUES          (?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$object->getObjectID(),
			serialize($data)
		]);
	}
	
	/**
	 * Creates the database tables to store each version.
	 */
	public function createStorageTables() {
		// get definition id
		$sql = "SELECT	definitionID
			FROM	wcf".WCF_N."_object_type_definition
			WHERE	definitionName = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
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
	 * Creates a database table for an object type unless it exists already.
	 * 
	 * @param       ObjectType      $objectType     target object type
	 * @return      boolean         false if table already exists
	 */
	protected function createStorageTable(ObjectType $objectType) {
		$baseTableName = $this->getTableName($objectType);
		$tableName = $baseTableName . '_version';
		
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
			['name' => 'data', 'data' => ['type' => 'longblob']]
		];
		
		WCF::getDB()->getEditor()->createTable($tableName, $columns);
		WCF::getDB()->getEditor()->addForeignKey(
			$tableName,
			md5($tableName . '_objectID') . '_fk',
			[
				'columns' => 'objectID',
				'referencedTable' => $baseTableName,
				'referencedColumns' => $objectType->tablePrimaryKey,
				'ON DELETE' => 'CASCADE'
			]
		);
		
		// add comment
		$sql = "ALTER TABLE	".$tableName."
			COMMENT		= 'Version tracking for ".$objectType->objectType."'";
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
	
	/**
	 * Retrieves the database table name.
	 * 
	 * @param       ObjectType      $objectType     target object type
	 * @return      string          database table name
	 */
	protected function getTableName(ObjectType $objectType) {
		static $packages;
		if ($packages === null) {
			$packageList = new PackageList();
			$packageList->getConditionBuilder()->add('package.isApplication = ?', [1]);
			$packageList->readObjects();
			
			$packages = $packageList->getObjects();
		}
		
		$tableName = $objectType->tableName;
		
		// replace app1_ with app{WCF_N}_ in the table name
		foreach ($packages as $package) {
			$abbreviation = Package::getAbbreviation($package->package);
			
			$tableName = str_replace($abbreviation.'1_', $abbreviation.WCF_N.'_', $tableName);
		}
		
		return $tableName;
	}
	
	/**
	 * Retrieves the object type object by its name.
	 * 
	 * @param       string          $name   object type name
	 * @return      ObjectType      target object
	 * @throws      SystemException
	 */
	protected function getObjectType($name) {
		foreach ($this->availableObjectTypes as $objectType) {
			if ($objectType->objectType === $name) {
				return $objectType;
			}
		}
		
		throw new SystemException("Unknown object type '".$name."' for definition 'com.woltlab.wcf.versionTracker.objectType'.");
	}
}
