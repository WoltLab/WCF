<?php
namespace wcf\data;

/**
 * Default interface for DatabaseObject processors.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data
 */
interface IDatabaseObjectProcessor {
	/**
	 * Creates a new instance of a database object processor.
	 * 
	 * @param	\wcf\data\DatabaseObject		$object
	 */
	public function __construct(DatabaseObject $object);
	
	/**
	 * Delegates accesses to inaccessible object properties the processed object.
	 * 
	 * @param	string		$name
	 * @return	mixed
	 */
	public function __get($name);
	
	/**
	 * Delegates isset calls for inaccessible object properties to the processed
	 * object.
	 * 
	 * @param	string		$name
	 * @return	boolean
	 */
	public function __isset($name);
	
	/**
	 * Delegates inaccessible method calls to the processed database object.
	 * 
	 * @param	string		$name
	 * @param	array		$arguments
	 * @return	mixed
	 */
	public function __call($name, $arguments);
}
