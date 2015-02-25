<?php
namespace wcf\system\search;

/**
 * Default interface for search index managers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.search
 * @category	Community Framework
 */
interface ISearchIndexManager {
	/**
	 * Adds a new entry.
	 * 
	 * @param	string		$objectType
	 * @param	integer		$objectID
	 * @param	string		$message
	 * @param	string		$subject
	 * @param	integer		$time
	 * @param	integer		$userID
	 * @param	string		$username
	 * @param	integer		$languageID
	 * @param	string		$metaData
	 */
	public function add($objectType, $objectID, $message, $subject, $time, $userID, $username, $languageID = null, $metaData = '');
	
	/**
	 * Updates the search index.
	 * 
	 * @param	string		$objectType
	 * @param	integer		$objectID
	 * @param	string		$message
	 * @param	string		$subject
	 * @param	integer		$time
	 * @param	integer		$userID
	 * @param	string		$username
	 * @param	integer		$languageID
	 * @param	string		$metaData
	 */
	public function update($objectType, $objectID, $message, $subject, $time, $userID, $username, $languageID = null, $metaData = '');
	
	/**
	 * Deletes search index entries.
	 * 
	 * @param	string		$objectType
	 * @param	array<integer>	$objectIDs
	 */
	public function delete($objectType, array $objectIDs);
	
	/**
	 * Resets the search index.
	 * 
	 * @param	string		$objectType
	 */
	public function reset($objectType);
	
	/**
	 * Creates the search index for all searchable objects.
	 */
	public function createSearchIndices();
	
	/**
	 * Begins the bulk operation.
	 */
	public function beginBulkOperation();
	
	/**
	 * Commits the bulk operation.
	 */
	public function commitBulkOperation();
}
