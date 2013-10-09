<?php
namespace wcf\system\io;
use wcf\system\exception\SystemException;

/**
 * The RemoteFile class opens a connection to a remote host as a file.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.io
 * @category	Community Framework
 */
class RemoteFile extends File {
	/**
	 * host address
	 * @var	string
	 */
	protected $host = '';
	
	/**
	 * port
	 * @var	integer
	 */
	protected $port = 0;
	
	/**
	 * error number
	 * @var	integer
	 */
	protected $errorNumber = 0;
	
	/**
	 * error description
	 * @var	string
	 */
	protected $errorDesc = '';
	
	/**
	 * Opens a new connection to a remote host.
	 * 
	 * @param	string		$host
	 * @param	integer		$port
	 * @param	integer		$timeout
	 * @param	array		$options
	 */
	public function __construct($host, $port, $timeout = 30) {
		$this->host = $host;
		$this->port = $port;
		
		if (NETWORK_INTERFACE_IP != '' && function_exists('stream_context_create') && function_exists('stream_socket_client')) {
			if (filter_var(NETWORK_INTERFACE_IP, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
				$bindTo = '[' . NETWORK_INTERFACE_IP . ']:' . NETWORK_INTERFACE_PORT;
			}
			else if (filter_var(NETWORK_INTERFACE_IP, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
				$bindTo = NETWORK_INTERFACE_IP . ':' . NETWORK_INTERFACE_PORT;
			}
			
			$options = array(
				'socket' => array(
					'bindto' => $bindTo
				)
			);
			
			$context = @stream_context_create($options);
			$this->resource = @stream_socket_client($this->host . ':' . $this->port, $this->errorNumber, $this->errorDesc, $timeout, STREAM_CLIENT_CONNECT, $context);
		}
		else {
			$this->resource = @fsockopen($this->host, $this->port, $this->errorNumber, $this->errorDesc, $timeout);
		}

		if ($this->resource === false) {
			throw new SystemException('Can not connect to ' . $this->host . ':' . $this->port, 0, $this->errorDesc);
		}
	}
	
	/**
	 * Returns the error number of the last error.
	 * 
	 * @return	integer
	 */
	public function getErrorNumber() {
		return $this->errorNumber;
	}
	
	/**
	 * Returns the error description of the last error.
	 * 
	 * @return	string
	 */
	public function getErrorDesc() {
		return $this->errorDesc;
	}
}
