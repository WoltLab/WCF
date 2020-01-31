<?php

namespace wcf\system\event\listener;

/**
 * Interface for a dynamic event. The function will be called with the name `onEventName`, for example the event is `foo`
 * the callback function `onFoo` would call with the parameters `($eventObj, array &$parameters)` => '::onFoo($eventObj, array &$parameters)`
 * 
 * @author	Olaf Braun
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Event\Listener
 */
interface IDynamicInvocationEventListener {
	
}
