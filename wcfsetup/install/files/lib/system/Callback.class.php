<?php
namespace wcf\system;
use wcf\system\exception\SystemException;

/**
 * Represents a callback
 * 
 * @author	Tim Düsterhus
 * @copyright	2011 Tim Düsterhus
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system
 * @category	Community Framework
 */
final class Callback {
	/**
	 * encapsulated callback
	 * @var	callback
	 */
	private $callback;
	
	/**
	 * the object the closure is bound to
	 * @var	object
	 */
	private $boundObject;
	
	/**
	 * Creates new instance of Callback.
	 * 
	 * @param	callback	$callback
	 * @param	object		$boundObject
	 */
	public function __construct($callback, $boundObject = null) {
		if (!is_callable($callback)) {
			throw new SystemException('Given callback is not callable.');
		}
		
		if (!is_null($boundObject) && !is_object($boundObject)) {
			throw new SystemException("Can't bind the callback to a non-object (".gettype($boundObject)." given).");
		}
		
		// TODO: When upgrading to PHP 5.4, use $callback->bindTo($boundObject) instead
		// Meanwhile the bound object is passed as first argument to the callback
		$this->callback = $callback;
		$this->boundObject = $boundObject;
	}
	
	/**
	 * Invokes our callback. All parameters are simply passed through.
	 * 
	 * @return	mixed
	 */
	public function __invoke() {
		$arguments = func_get_args();
		if (!is_null($this->boundObject)) {
			array_unshift($arguments, $this->boundObject);
		}
		return call_user_func_array($this->callback, $arguments);
	}
	
	/**
	 * Returns the object this callback is bound to.
	 * 
	 * @return	object
	 */
	public function getBoundObject() {
		return $this->boundObject;
	}
}
