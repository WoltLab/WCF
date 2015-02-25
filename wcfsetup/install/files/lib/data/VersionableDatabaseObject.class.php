<?php
namespace wcf\data;
use wcf\system\exception\SystemException;
use wcf\system\version\VersionHandler;

/**
 * Abstract class for all versionable data classes.
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
abstract class VersionableDatabaseObject extends DatabaseObject {
	/**
	 * name of the versionable object type
	 * @var	string
	 */
	public $versionableObjectTypeName = '';
	
	/**
	 * Returns suffix of the version database table.
	 * 
	 * @return	string
	 */
	public static function getDatabaseVersionTableName() {
		return static::getDatabaseTableName().'_version';
	}
	
	/**
	 * Returns name of index in version database table.
	 * 
	 * @return	string
	 */
	public static function getDatabaseVersionTableIndexName() {
		return 'versionID';
	}
	
	/**
	 * Returns all versions of this database object.
	 * 
	 * @return	array<\wcf\data\VersionableDatabaseObject>
	 */
	public function getVersions() {
		$objectType = VersionHandler::getInstance()->getObjectTypeByName($this->versionableObjectTypeName);
		
		if ($objectType === null) {
			throw new SystemException("Unknown versionable object type with name '".$this->versionableObjectTypeName."'");
		}
		
		return VersionHandler::getInstance()->getVersions($objectType->objectTypeID, $this->getObjectID());
	}
}
