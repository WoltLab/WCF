<?php
namespace wcf\system\event;

/**
 * *DEPRECATED*
 * EventListeners can be registered for a specific event in many controller objects.
 * 
 * @deprecated	2.1, use \wcf\system\event\listener\IParameterizedEventListener
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Event
 */
interface IEventListener {
	/**
	 * Executes this action.
	 * 
	 * @param	object		$eventObj
	 * @param	string		$className
	 * @param	string		$eventName
	 */
	public function execute($eventObj, $className, $eventName);
}
