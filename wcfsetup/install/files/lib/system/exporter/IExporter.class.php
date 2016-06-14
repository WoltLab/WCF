<?php
namespace wcf\system\exporter;

/**
 * Basic interface for all exporters.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Exporter
 */
interface IExporter {
	/**
	 * Sets database access data.
	 * 
	 * @param	string		$databaseHost
	 * @param	string		$databaseUser
	 * @param	string		$databasePassword
	 * @param	string		$databaseName
	 * @param	string		$databasePrefix
	 * @param	string		$fileSystemPath
	 * @param	array		$additionalData
	 */
	public function setData($databaseHost, $databaseUser, $databasePassword, $databaseName, $databasePrefix, $fileSystemPath, $additionalData);
	
	/**
	 * Initializes this exporter.
	 */
	public function init();
	
	/**
	 * Counts the number of required loops for given type.
	 * 
	 * @param	string		$objectType
	 * @return	integer
	 */
	public function countLoops($objectType);
	
	/**
	 * Runs the data export.
	 * 
	 * @param	string		$objectType
	 * @param	integer		$loopCount
	 */
	public function exportData($objectType, $loopCount = 0);
	
	/**
	 * Validates database access.
	 * 
	 * @throws	\wcf\system\database\exception\DatabaseException
	 */
	public function validateDatabaseAccess();
	
	/**
	 * Validates given file system path. Returns false on failure.
	 * 
	 * @return	boolean
	 */
	public function validateFileAccess();
	
	/**
	 * Validates the selected data types. Returns false on failure.
	 * 
	 * @param	array		$selectedData
	 * @return	boolean
	 */
	public function validateSelectedData(array $selectedData);
	
	/**
	 * Returns the import worker queue.
	 * 
	 * @return	array
	 */
	public function getQueue();
	
	/**
	 * Returns the supported data types.
	 * 
	 * @return	string[]
	 */
	public function getSupportedData();
	
	/**
	 * Returns a default database table prefix.
	 * 
	 * @return	string
	 */
	public function getDefaultDatabasePrefix();
}
