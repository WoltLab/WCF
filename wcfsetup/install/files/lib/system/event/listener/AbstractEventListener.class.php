<?php
namespace wcf\system\event\listener;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Preferred implementation for event listeners that dynamically invokes methods based on a predictable
 * naming schema derived from the event name. In addition, `AbstractDatabaseObjectAction` supports deep
 * method invocations for the generic action events. However, when there is no specific method, it will
 * attempt to invoke the regular method instead.
 * 
 * Examples:
 * Regular classes
 *   eventName: makeSnafucated
 *   derived method: onMakeSnafucated()
 * 
 * Classes deriving from AbstractDatabaseObjectAction
 *   eventName: initializeAction
 *   actionName: makeSnafucated
 *   derived method: onInitializeActionMakeSnafucated()
 *   ATTENTION: If this method does not exist, it will attempt to invoke `onInitializeAction()` instead. 
 *
 * @author      Olaf Braun, Alexander Ebert
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Event\Listener
 */
abstract class AbstractEventListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public final function execute($eventObj, $className, $eventName, array &$parameters) {
		static $genericDboActionNames = ['finalizeAction', 'initializeAction', 'validateAction'];
		
		$methodName = 'on' . ucfirst($eventName);
		
		if ($eventObj instanceof AbstractDatabaseObjectAction && in_array($eventName, $genericDboActionNames)) {
			$actionMethod = $methodName . ucfirst($eventObj->getActionName());
			if (method_exists($this, $actionMethod)) {
				$this->{$actionMethod}($eventObj, $parameters);
				return;
			}
		}
		
		if (method_exists($this, $methodName)) {
			$this->{$methodName}($eventObj, $parameters);
		}
	}
}
