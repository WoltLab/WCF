<?php
namespace wcf\system\cleanup;
use wcf\system\cache\CacheHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\event\EventHandler;
use wcf\system\exception\SystemException;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\ClassUtil;

/**
 * Handles cleanup related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cleanup
 * @category 	Community Framework
 */
class CleanupHandler {
	/**
	 * unique instance of CleanupHandler
	 * @var	wcf\system\cleanup\CleanupHandler
	 */
	protected static $instance = null;
	
	/**
	 * cleanup adapter cache
	 * @var	array<array>
	 */
	protected $cache = null;
	
	/**
	 * Initializes cleanup handler.
	 */
	protected function __construct() {
		$this->loadCache();
	}
	
	/**
	 * Prevents creating an additional instance.
	 */
	protected function __clone() {}
	
	/**
	 * Loads cleanup adapter cache.
	 */
	protected function loadCache() {
		CacheHandler::getInstance()->addResource(
			'cleanupAdapter-'.PACKAGE_ID,
			WCF_DIR.'cache/cache.cleanupAdapter.php',
			'wcf\system\cache\builder\CacheBuilderCleanupAdapter'
		);
		
		$this->cache = CacheHandler::getInstance()->get('cleanupAdapter');
	}
	
	/**
	 * Prepares adapter execution
	 */
	public function execute() {
		// remove all logged items older than 24 hours
		$sql = "DELETE FROM	wcf".WCF_N."_cleanup_log
			WHERE		deleteTime < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			(TIME_NOW - 86400)
		));
		
		// loop through all available adapters
		foreach ($this->cache['adapters'] as $objectType => $adapters) {
			// determine if there are any items for current object type
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("objectType = ?", array($objectType));
			$conditions->add("packageID IN (?)", array($this->cache['objectTypes'][$objectType]));
			
			$sql = "SELECT	objectID
				FROM	wcf".WCF_N."_cleanup_log
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			
			$objectIDs = array();
			while ($row = $statement->fetchArray()) {
				$objectIDs[] = $row['objectID'];
			}
			
			if (count($objectIDs)) {
				$this->executeAdapters($adapters, $objectIDs);
			}
		}
	}
	
	/**
	 * Executes specific cleanup adapters.
	 * 
	 * @param	array		$adapters
	 * @param	array		$objectIDs
	 */
	protected function executeAdapters(array $adapters, array $objectIDs) {
		$sql = "UPDATE	wcf".WCF_N."_cleanup_listener
			SET	lastUpdateTime = ?
			WHERE	listenerID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		foreach ($adapters as $adapterData) {
			// validate class
			if (!class_exists($adapterData['className'])) {
				throw new SystemException("unable to find class '".$adapterData['className']."'", 11001);
			}
			
			// validate interface
			if (!(ClassUtil::isInstanceOf($adapterData['className'], 'wcf\system\cleanup\ICleanupAdapter'))) {
				throw new SystemException("class '".$adapterData['className']."' does not implement the interface 'wcf\system\cleanup\ICleanupAdapter'", 11010);
			}
			
			$adapter = new $adapterData['className']();
			$adapter->execute($objectIDs);
			
			// update last time of execution
			$statement->execute(array(TIME_NOW, $adapterData['listenerID']));
		}
	}
	
	/**
	 * Returns an unique instance of CleanupHandler.
	 * 
	 * @return	wcf\system\cleanup\CleanupHandler
	 */
	public static function getInstance() {
		if (self::$instance === null) {
			// call loadInstance event
			EventHandler::getInstance()->fireAction(__CLASS__, 'loadInstance');
			
			if (self::$instance === null) {
				self::$instance = new CleanupHandler();
			}
		}
		
		return self::$instance;
	}
	
	/**
	 * Registers deleted objects.
	 * 
	 * @param	string		$objectType
	 * @param	array		$objectIDs
	 * @param	integer		$packageID
	 */
	public static function registerObjects($objectType, array $objectIDs, $packageID) {
		$objectIDs = ArrayUtil::toIntegerArray($objectIDs);
		$packageID = intval($packageID);
		
		// insert items
		$sql = "INSERT INTO	wcf".WCF_N."_cleanup_log
					(packageID, objectType, objectID, deleteTime)
			VALUES		(?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		foreach ($objectIDs as $objectID) {
			$statement->execute(array(
				$packageID,
				$objectType,
				$objectID,
				TIME_NOW
			));
		}
	}
}
