<?php
namespace wcf\system\cache\builder;
use wcf\system\event\listener\EventHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Caches the event listeners.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category 	Community Framework
 */
class EventListenerCacheBuilder implements ICacheBuilder {
	/**
	 * @see wcf\system\cache\ICacheBuilder::getData()
	 */
	public function getData(array $cacheResource) {
		list($cache, $packageID) = explode('-', $cacheResource['cache']); 
		$data = array(
			'actions' => array('user' => array(), 'admin' => array()),
			'inheritedActions' => array('user' => array(), 'admin' => array())
		);
		
		// get all listeners and filter options with low priority
		$sql = "SELECT		event_listener.*
			FROM		wcf".WCF_N."_event_listener event_listener
			LEFT JOIN	wcf".WCF_N."_package_dependency package_dependency
			ON		(package_dependency.dependency = event_listener.packageID)
			WHERE 		package_dependency.packageID = ?
			ORDER BY	package_dependency.priority ASC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($packageID));
		while ($row = $statement->fetchArray()) {
			// distinguish between inherited actions and non-inherited actions
			if (!$row['inherit']) {
				$data['actions'][EventHandler::generateKey($row['eventClassName'], $row['eventName'])][] = $row;
			}
			else {
				if (!isset($data['inheritedActions'][$row['eventClassName']])) $data['inheritedActions'][$row['eventClassName']] = array();
				$data['inheritedActions'][$row['eventClassName']][$row['eventName']][] = $row;	
			}
		}
		
		// sort data by nice value and class name
		foreach ($data['actions'] as $key => $listeners) {
			uasort($data['actions'][$key], array(__CLASS__, 'sortListeners'));
		}
		
		foreach ($data['inheritedActions'] as $class => $listeners) {
			foreach ($listeners as $key => $val) {
				uasort($data['inheritedActions'][$class][$key], array(__CLASS__, 'sortListeners'));
			}
		}
		
		return $data;
	}
	
	/**
	 * Sorts the event listeners alphabetically.
	 */
	public static function sortListeners($listenerA, $listenerB) {
		if ($listenerA['niceValue'] < $listenerB['niceValue']) {
			return -1;
		}
		else if ($listenerA['niceValue'] > $listenerB['niceValue']) {
			return 1;
		}
		else {
			return strcmp($listenerA['listenerClassName'], $listenerB['listenerClassName']);
		}	
	}
}
