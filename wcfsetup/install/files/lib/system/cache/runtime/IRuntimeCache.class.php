<?php
namespace wcf\system\cache\runtime;
use wcf\data\DatabaseObject;

/**
 * Handles runtime caches to centrally store objects fetched during tuntime for reuse.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Runtime
 * @since	3.0
 */
interface IRuntimeCache {
	/**
	 * Caches the given object id so that during the next object fetch, the object with
	 * this id will also be fetched.
	 * 
	 * @param	integer		$objectID
	 */
	public function cacheObjectID($objectID);
	
	/**
	 * Caches the given object ids so that during the next object fetch, the objects with
	 * these ids will also be fetched.
	 * 
	 * @param	integer[]	$objectIDs
	 */
	public function cacheObjectIDs(array $objectIDs);
	
	/**
	 * Returns all currently cached objects.
	 * 
	 * @return	DatabaseObject[]
	 */
	public function getCachedObjects();
	
	/**
	 * Returns the object with the given id or null if no such object exists.
	 * If the given object id should not have been cached before, it will be cached
	 * during this method call and the object, if existing, will be returned.
	 * 
	 * @param	integer		$objectID
	 * @return	DatabaseObject|null
	 */
	public function getObject($objectID);
	
	/**
	 * Returns the objects with the given ids. If an object does not exist, the array element
	 * wil be null.
	 * If the given object ids should not have been cached before, they will be cached
	 * during this method call and the objects, if existing, will be returned.
	 * 
	 * @param	integer[]	$objectIDs
	 * @return	DatabaseObject[]
	 */
	public function getObjects(array $objectIDs);
	
	/**
	 * Removes the object with the given id from the runtime cache if it has already been loaded.
	 * 
	 * @param	integer		$objectID
	 */
	public function removeObject($objectID);
	
	
	/**
	 * Removes the objects with the given ids from the runtime cache if they have already been loaded.
	 *
	 * @param	integer[]	$objectIDs
	 */
	public function removeObjects(array $objectIDs);
}
