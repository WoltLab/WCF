<?php
namespace wcf\system\event\listener;

/**
 * EventListeners can be registered for a specific event in many controller objects.
 * NOTE: This class will be aliased to \wcf\system\event\listener\IEventListener in
 *       a future version. It is named IParameterizedEventListener for backwards
 *       compatibility reasons only.
 * 
 * @author	Tim Duesterhus, Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Event\Listener
 */
interface IParameterizedEventListener {
	/**
	 * Executes this action.
	 * 
	 * @param	object		$eventObj	Object firing the event
	 * @param	string		$className	class name of $eventObj
	 * @param	string		$eventName	name of the event fired
	 * @param	array		&$parameters	given parameters
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters);
}
