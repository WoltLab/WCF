<?php
namespace wcf\system\event\listener;

/**
 * EventListeners can be registered for a specific event in many controller objects.
 * 
 * @author	Tim Duesterhus, Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.event.listener
 * @category	Community Framework
 */
interface IEventListener {
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
