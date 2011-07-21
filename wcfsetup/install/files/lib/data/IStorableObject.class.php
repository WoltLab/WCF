<?php
namespace wcf\data;

/**
 * Abstract class for all data holder classes.
 *
 * @author	Marcel Werk
 * @copyright	2001-2010 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data
 * @category 	Community Framework
 */
interface IStorableObject {
	/**
	 * Returns the value of a variable in object data.
	 * 
	 * @param	string		$name		variable name
	 * @return	mixed		value
	 */
	public function __get($name);
	
	/**
	 * Determines if a variable is set and is not NULL.
	 * 
	 * @param	string		$name		variable name
	 * @return	boolean
	 */
	public function __isset($name);
	
	/**
	 * Returns the name of the database table.
	 * 
	 * @return string
	 */
	public static function getDatabaseTableName();
	
	/**
	 * Returns the alias of the database table.
	 * 
	 * @return	string
	 */
	public static function getDatabaseTableAlias();
	
	/**
	 * Returns true if database table index is an identity column.
	 * 
	 * @return	boolean
	 */
	public static function getDatabaseTableIndexIsIdentity();
	
	/**
	 * Returns the name of the database table index.
	 * 
	 * @return string
	 */
	public static function getDatabaseTableIndexName();
}
