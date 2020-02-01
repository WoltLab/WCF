<?php
namespace wcf\system\event\listener;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Special event listener for `\wcf\data\AbstractDatabaseObjectAction` events.
 * This appends behind the called function name the action of the database object
 *
 * Using:
 *      - Event Name = finalizeAction
 *      - \wcf\data\AbstractDatabaseObjectAction::$action = create
 *      =>  ::onFinalizeActionCreate($eventObj, array &$parameters)
 *
 * @author       Olaf Braun
 * @copyright    2001-2019 WoltLab GmbH
 * @license      GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package      WoltLabSuite\Core\System\Event\Listener
 * @since	     5.3
 */
abstract class AbstractDynamicDatabaseObjectEventListener extends AbstractDynamicInvocationEventListener {
	/**
	 * @param AbstractDatabaseObjectAction $eventObj
	 * @param array                        $parameters
	 */
	protected function onFinalizeAction($eventObj, array &$parameters) {
		$this->callDynamicActionEvent(__FUNCTION__, $eventObj, $parameters);
	}
	
	/**
	 * @param AbstractDatabaseObjectAction $eventObj
	 * @param array                        $parameters
	 */
	protected function onValidateAction($eventObj, array &$parameters) {
		$this->callDynamicActionEvent(__FUNCTION__, $eventObj, $parameters);
	}
	
	/**
	 * Call dynamic the action event, if the function exist in this class
	 *
	 * @param string                       $functionName
	 * @param AbstractDatabaseObjectAction $eventObj
	 * @param array                        $parameters
	 */
	protected final function callDynamicActionEvent($functionName, $eventObj, array &$parameters) {
		if (!($eventObj instanceof AbstractDatabaseObjectAction)) return;
		
		$functionName .= ucfirst($eventObj->getActionName());
		if (method_exists($this, $functionName)) {
			$this->{$functionName}($eventObj, $parameters);
		}
	}
}
