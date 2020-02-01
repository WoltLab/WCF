<?php
namespace wcf\system\event\listener;

/**
 * Dynamic event listener, will call the function by its event name, for example the event is `foo`
 * the method `onFoo` would call with the parameters `($eventObj, array &$parameters)` => '::onFoo($eventObj, array &$parameters)`
 * 
 * @author       Olaf Braun
 * @copyright    2001-2019 WoltLab GmbH
 * @license      GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package      WoltLabSuite\Core\System\Event\Listener
 * @since	     5.3
 */
abstract class AbstractDynamicInvocationEventListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		$functionName = "on" . ucfirst($eventName);
		
		if (method_exists($this, $functionName)) {
			$this->{$functionName}($eventObj, $parameters);
		}
	}
}
