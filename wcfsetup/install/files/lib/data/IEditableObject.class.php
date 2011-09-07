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
interface IEditableObject extends IStorableObject {
	/**
	 * Creates a new object.
	 * 
	 * @param	array		$parameters
	 * @return	wcf\data\IStorableObject
	 */
	public static function create(array $parameters = array());
	
	/**
	 * Updates this object.
	 * 
	 * @param	array		$parameters
	 */
	public function update(array $parameters = array());
	
	/**
	 * Deletes this object.
	 */
	public function delete();
	
	/**
	 * Deletes all the objects with the given ids and returns the number of
	 * deleted objects.
	 * 
	 * @param	array		$objectIDs
	 * @return	integer
	 */
	public static function deleteAll(array $objectIDs = array());
}
