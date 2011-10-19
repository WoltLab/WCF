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
	 * The callback
	 *
	 * @var	callback
	 */
	private $callback = null;
	
	/**
	 * Checks whether the callback is callable.
	 *
	 * @param	callback	$callback
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
