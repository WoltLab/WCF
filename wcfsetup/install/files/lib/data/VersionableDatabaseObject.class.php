<?php
namespace wcf\data;
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
	 * name of the object type
	 * @var	string
	 */
	public $objectTypeName = '';
	
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
}
