<?php
namespace wcf\system;
use wcf\system\exception\SystemException;

/**
 * Represents a callback
 * 
 * @author	Tim Duesterhus
 * @copyright	2011 Tim Duesterhus
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System
 */
final class Callback {
	/**
	 * encapsulated callback
	 * @var	callback
	 */
	private $callback = null;
	
	/**
	 * Creates new instance of Callback.
	 * 
	 * @param	callback	$callback
	 * @throws	SystemException
	 */
	public function __construct($callback) {
		if (!is_callable($callback)) {
			throw new SystemException('Given callback is not callable.');
		}
		
		$this->callback = $callback;
	}
	
	/**
	 * Invokes our callback. All parameters are simply passed through.
	 * 
	 * @return	mixed
	 */
	public function __invoke() {
		return call_user_func_array($this->callback, func_get_args());
	}
}
