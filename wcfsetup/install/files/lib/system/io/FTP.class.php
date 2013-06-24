<?php
namespace wcf\system\io;
use wcf\system\exception\SystemException;

/**
 * The FTP class handles all ftp operations.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.io
 * @category	Community Framework
 */
class FTP {
	/**
	 * file pointer resource
	 * @var	resource
	 */
	protected $resource = null;
	
	/**
	 * Opens a new ftp connection to given host.
	 * 
	 * @param	string		$host
	 * @param	string		$port
	 * @param	integer		$timeout
	 */
	public function __construct($host = 'localhost', $port = 21, $timeout = 30) {
		$this->resource = ftp_connect($host, $port, $timeout);
		if ($this->resource === false) {
			throw new SystemException('Can not connect to ' . $host);
		}
	}
	
	/**
	 * Calls the specified function on the open ftp connection.
	 * 
	 * @param	string		$function
	 * @param	array		$arguments
	 */
	public function __call($function, $arguments) {
		array_unshift($arguments, $this->resource);
		if (!function_exists('ftp_'.$function)) {
			throw new SystemException('Can not call method ' . $function);
		}
		
		return call_user_func_array('ftp_' . $function, $arguments);
	}
}
