<?php
namespace wcf\system\event\listener;

/**
 * Dynamic event listener, will call the function by his event name, for example the event is `foo`
 * the method `onFoo` would call with the parameters `(array &$parameters)` => '::onFoo(array &$parameters)`
 * 
 * @author       Olaf Braun
 * @copyright    2001-2019 WoltLab GmbH
 * @license      GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package      WoltLabSuite\Core\System\Event\Listener
 */
abstract class AbstractDynamicInvocationEventListener implements IParameterizedEventListener {
	
	/**
	 * Event object
	 *
	 * @var Object
	 */
	protected $eventObj;
	/**
	 * @var string
	 */
	protected $className;
	
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		$this->eventObj = $eventObj;
		$this->className = $className;
		$functionName = "on" . ucfirst($eventName);
		
		if (method_exists($this, $functionName)) {
			$this->{$functionName}($parameters);
		}
	}
}
