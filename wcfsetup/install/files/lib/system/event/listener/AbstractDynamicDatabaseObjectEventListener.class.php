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
 */
abstract class AbstractDynamicDatabaseObjectEventListener extends AbstractDynamicInvocationEventListener {
	
	/**
	 * @var AbstractDatabaseObjectAction
	 */
	protected $eventObj;
	
	/**
	 * @param AbstractDatabaseObjectAction $eventObj
	 * @param array                        $parameters
	 */
	protected function onFinalizeAction($eventObj, &$parameters) {
		$this->callDynamicActionEvent(__FUNCTION__, $eventObj, $parameters);
	}
	
	/**
	 * @param AbstractDatabaseObjectAction $eventObj
	 * @param array                        $parameters
	 */
	protected function onValidateAction($eventObj, &$parameters) {
		$this->callDynamicActionEvent(__FUNCTION__, $eventObj, $parameters);
	}
	
	/**
	 * Call dynamic the action event, if the function exist in this class
	 *
	 * @param string                       $functionName
	 * @param AbstractDatabaseObjectAction $eventObj
	 * @param array                        $parameters
	 */
	protected final function callDynamicActionEvent($functionName, $eventObj, &$parameters) {
		if (!$eventObj instanceof AbstractDatabaseObjectAction) return;
		
		$this->eventObj = $eventObj;
		$functionName .= ucfirst($this->eventObj->getActionName());
		if (method_exists($this, $functionName)) {
			$this->{$functionName}($parameters);
		}
	}
}
