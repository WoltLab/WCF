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
	 * @param 	string		$host
	 * @param 	integer		$port
	 * @param 	integer		$timeout
	 * @param	array		$options
	 */
	public function __construct($host, $port, $timeout = 30, $options = array()) {
		$this->host = $host;
		$this->port = $port;
		if (count($options)) {
			$context = stream_context_create($options);
			$this->resource = stream_socket_client($host, $port, $this->errorNumber, $this->errorDesc, $timeout, $context);
		}
		else {
			$this->resource = fsockopen($host, $port, $this->errorNumber, $this->errorDesc, $timeout);
		}
		if ($this->resource === false) {
			throw new SystemException('Can not connect to ' . $host);
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
