<?php
namespace wcf\system\event;
use wcf\system\cache\CacheHandler;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\util\ClassUtil;

/**
 * EventHandler executes all registered actions for a specific event.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.event
 * @category 	Community Framework
 */
class EventHandler extends SingletonFactory {
	/**
	 * Registerd actions.
	 *
	 * @var array
	 */
	protected $actions = null;
	
	/**
	 * Registerd inherit actions.
	 *
	 * @var array
	 */
	protected $inheritedActions = null;
	
	/**
	 * Instances of registerd actions.
	 *
	 * @var array
	 */
	protected $actionsObjects = array();
	
	/**
	 * Instances of registerd inherit actions.
	 *
	 * @var array
	 */
	protected $inheritedActionsObjects = array();
	
	/**
	 * Instances of action objects.
	 *
	 * @var array
	 */
	protected $listenerObjects = array();
	
	/**
	 * Loads all registered actions of the active package.
	 */
	protected function loadActions() {
		CacheHandler::getInstance()->addResource('eventListener-'.PACKAGE_ID, WCF_DIR.'cache/cache.eventListener-'.PACKAGE_ID.'.php', 'wcf\system\cache\builder\CacheBuilderEventListener');
		$cache = CacheHandler::getInstance()->get('eventListener-'.PACKAGE_ID);
		if (isset($cache['actions'])) $this->actions = $cache['actions'];
		if (isset($cache['inheritedActions'])) $this->inheritedActions = $cache['inheritedActions'];
		unset($cache);
		if (!is_array($this->actions)) $this->actions = array();
		if (!is_array($this->inheritedActions)) $this->inheritedActions = array();
	}
	
	/**
	 * Executes all inherited listeners for the given event.
	 *
	 * @param	mixed		$eventObj
	 * @param	string		$eventName
	 * @param	string		$className
	 * @param	string		$name
	 */
	protected function executeInheritedActions($eventObj, $eventName, $className, $name) {
		// create objects of the actions
		if (!isset($this->inheritedActionsObjects[$name]) || !is_array($this->inheritedActionsObjects[$name])) {
			$this->inheritedActionsObjects[$name] = array();

			// get parent classes
			$familyTree = array();
			$member = (is_object($eventObj) ? get_class($eventObj) : $eventObj);
			while ($member != false) {
				$familyTree[] = $member;
				$member = get_parent_class($member);
			}

			foreach ($familyTree as $member) {
				if (isset($this->inheritedActions[$member])) {
					$actions = $this->inheritedActions[$member];
					if (isset($actions[$eventName]) && count($actions[$eventName]) > 0) {
						foreach ($actions[$eventName] as $action) {
							if (isset($this->inheritedActionsObjects[$name][$action['listenerClassName']])) continue;

							// get class object
							if (isset($this->listenerObjects[$action['listenerClassName']])) {
								$object = $this->listenerObjects[$action['listenerClassName']];
							}
							else {
								$object = null;
								// instance action object
								if (!class_exists($action['listenerClassName'])) {
									throw new SystemException("Unable to find class '".$action['listenerClassName']."'", 11001);
								}
								if (!ClassUtil::isInstanceOf($action['listenerClassName'], 'wcf\system\event\IEventListener')) {
									throw new SystemException("'".$action['listenerClassName']."' should implement interface wcf\system\event\IEventListener");
								}
	
								$object = new $action['listenerClassName'];
								$this->listenerObjects[] = $object;
							}
							
							if ($object !== null) $this->inheritedActionsObjects[$name][$action['listenerClassName']] = $object;
						}
					}
				}
			}
		}
		
		// execute actions
		foreach ($this->inheritedActionsObjects[$name] as $actionObj) {
			$actionObj->execute($eventObj, $className, $eventName);
		}
	}
	
	/**
	 * Executes all registered listeners for the given event.
	 *
	 * @param	mixed		$eventObj
	 * @param	string		$eventName
	 */
	public function fireAction($eventObj, $eventName) {
		// get class name
		if (is_object($eventObj)) $className = get_class($eventObj);
		else $className = $eventObj;
		
		// load actions from cache if necessary
		if ($this->actions === null && $this->inheritedActions === null) {
			$this->loadActions();
		}
		
		// generate action name
		$name = self::generateKey($className, $eventName);

		// execute inherited actions first
		if (count($this->inheritedActions) > 0) {
			$this->executeInheritedActions($eventObj, $eventName, $className, $name);
		}
		
		// create objects of the actions
		if (!isset($this->actionsObjects[$name]) || !is_array($this->actionsObjects[$name])) {
			if (!isset($this->actions[$name]) || !is_array($this->actions[$name])) {
				// no action registered
				return false;
			}
		
			$this->actionsObjects[$name] = array();
			foreach ($this->actions[$name] as $action) {
				if (isset($this->actionsObjects[$name][$action['listenerClassName']])) continue;

				// get class object
				if (isset($this->listenerObjects[$action['listenerClassName']])) {
					$object = $this->listenerObjects[$action['listenerClassName']];
				}
				else {
					// instance action object
					if (!class_exists($action['listenerClassName'])) {
						throw new SystemException("Unable to find class '".$action['listenerClassName']."'", 11001);
					}
					if (!ClassUtil::isInstanceOf($action['listenerClassName'], 'wcf\system\event\IEventListener')) {
						throw new SystemException("'".$action['listenerClassName']."' should implement interface wcf\system\event\IEventListener");
					}
				
					$object = new $action['listenerClassName'];
					$this->listenerObjects[$path] = $object;
				}
				
				$this->actionsObjects[$name][$action['listenerClassName']] = $object;
			}
		}
		
		// execute actions
		foreach ($this->actionsObjects[$name] as $actionObj) {
			$actionObj->execute($eventObj, $className, $eventName);
		}
	}
	
	/**
	 * Generates an unique name for an action.
	 *
	 * @param	string		$className
	 * @param	string		$eventName
	 */
	public static function generateKey($className, $eventName) {
		return $eventName.'@'.$className;
	}
}
