<?php
namespace wcf\system\event;

$_ = \wcf\functions\deprecatedClass(IEventListener::class);
/**
 * EventListeners can be registered for a specific event in many controller objects.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Event
 * @deprecated	2.1 - Use \wcf\system\event\listener\IParameterizedEventListener which takes an additional
 * 		`array &$parameters` parameter. Note the additional `listener` in the namespace.
 */
interface IEventListener {
	/**
	 * Executes this action.
	 * 
	 * @param	mixed		$eventObj
	 * @param	string		$className
	 * @param	string		$eventName
	 */
	public function execute($eventObj, $className, $eventName);
}
