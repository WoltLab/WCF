<?php
namespace wcf\data;

/**
 * Abstract class for all data holder classes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data
 */
interface IEditableObject extends IStorableObject {
	/**
	 * Creates a new object.
	 * 
	 * @param	array		$parameters
	 * @return	\wcf\data\IStorableObject
	 */
	public static function create(array $parameters = []);
	
	/**
	 * Updates this object.
	 * 
	 * @param	array		$parameters
	 */
	public function update(array $parameters = []);
	
	/**
	 * Updates the counters of this object.
	 * 
	 * @param	array		$counters
	 */
	public function updateCounters(array $counters = []);
	
	/**
	 * Deletes this object.
	 */
	public function delete();
	
	/**
	 * Deletes all objects with the given ids and returns the number of deleted
	 * objects.
	 * 
	 * @param	array		$objectIDs
	 * @return	integer
	 */
	public static function deleteAll(array $objectIDs = []);
}
