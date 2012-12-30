<?php
namespace wcf\data;
use wcf\system\version\VersionHandler;
use wcf\util\StringUtil;

/**
 * Abstract class for all versionable data classes.
 * 
 * @author	Jeffrey Reichardt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data
 * @category	Community Framework
 */
abstract class VersionableDatabaseObject extends DatabaseObject {	
	/**
	 * name of the versionable object type
	 * @var	string
	 */
	public $versionableObjectTypeName = '';
	
	/**
	 * Returns suffix of database tables.
	 * 
	 * @return	string
	 */
	protected static function getDatabaseVersionTableName() {
		return static::getDatabaseTableName().'_version';
	}
	
	/**
	 * Returns name of index in version table.
	 * 
	 * @return	string
	 */
	protected static function getDatabaseVersionTableIndexName() {
		return 'version'.ucfirst(static::getDatabaseIndexTableName());
	}
	
	/**
	 * Returns all versions of this database object
	 * 
	 * @return	array<wcf\data\VersionableDatabaseObject>
	 */
	public function getVersions() {
		$objectType = VersionHandler::getInstance()->getObjectTypeByName($this->versionableObjectTypeName);
		
		if ($objectType === null) {
			throw new SystemException("Unknown versionable object type with name '".$this->versionableObjectTypeName."'");
		}
		
		return VersionHandler::getInstance()->getVersions($objectType->objectTypeID, $this->getObjectID());
	}
}
