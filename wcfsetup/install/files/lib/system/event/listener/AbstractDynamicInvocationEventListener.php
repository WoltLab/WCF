<?php
namespace wcf\system\event\listener;

/**
 * An AbstractDynamicInvocationEventListener will automatically dispatch events to an event specific class method.
 *
 * The method name is constructed as `on` followed by the event name with an uppercased first letter. The method
 * will be passed the `$eventObj` and the `&$parameters` array.
 * It's the responsibility of the user to take the `$parameters` by reference.
 *
 * @author    Olaf Braun
 * @copyright 2001-2019 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package   WoltLabSuite\Core\System\Event\Listener
 * @since     5.3
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
