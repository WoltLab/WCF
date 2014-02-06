<?php
namespace wcf\system\event;

/**
 * EventListeners can be registered for a specific event in many controller objects.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.event
 * @category	Community Framework
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
