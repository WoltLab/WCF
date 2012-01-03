<?php
namespace wcf\system\io;
use wcf\system\exception\SystemException;

/**
 * The RemoteFile class opens a connection to a remote host as a file.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.io
 * @category 	Community Framework
 */
class RemoteFile extends File {
	/**
	 * socket transport protocol see http://www.php.net/manual/en/transports.php
	 * @var string
	 */
	protected $protocol = '';
	
	/**
	 * host address
	 * @var string
	 */
	protected $host = '';

	/**
	 * port
	 * @var integer
	 */
	protected $port = 0;

	/**
	 * error number
	 * @var integer
	 */
	protected $errorNumber = 0;

	/**
	 * error description
	 * @var string
	 */
	protected $errorDesc = '';

	/**
	 * Opens a new connection to a remote host.
	 *
	 * @param	string		$protocol
	 * @param 	string		$host
	 * @param 	integer		$port
	 * @param 	integer		$connectionTimeout
	 * @param	array		$options
	 * @param	integer		$flags
	 */
	public function __construct($protocol, $host, $port, $connectionTimeout = 30, $options = array(), $flags = STREAM_CLIENT_CONNECT) {
		$this->protocol = $protocol;
		$this->host = $host;
		$this->port = $port;
		$this->remoteSocket = $this->protocol.'://'.$this->host.':'.$this->port;

		if (count($options)) {
			$context = stream_context_create($options);
			$this->resource = stream_socket_client($this->remoteSocket, $this->errorNumber, $this->errorDesc, $connectionTimeout, $flags, $context);
		}
		else {
			$this->resource = stream_socket_client($this->remoteSocket, $this->errorNumber, $this->errorDesc, $connectionTimeout);
		}
		
		if ($this->resource === false) {
			throw new SystemException('Can not connect to ' . $host, 14000);
		}
	}

	/**
	 * Returns the error number of the last error.
	 *
	 * @return 	integer
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
