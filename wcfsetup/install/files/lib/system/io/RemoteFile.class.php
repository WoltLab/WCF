<?php
namespace wcf\system\io;
use wcf\system\exception\SystemException;

/**
 * The RemoteFile class opens a connection to a remote host as a file.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Io
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
	 * true if PHP supports SSL/TLS
	 * @var	boolean
	 */
	private static $hasSSLSupport = null;
	
	/** @noinspection PhpMissingParentConstructorInspection */
	/**
	 * Opens a new connection to a remote host.
	 * 
	 * @param	string		$host
	 * @param	integer		$port
	 * @param	integer		$timeout
	 * @param	array		$options
	 * @throws	SystemException
	 */
	public function __construct($host, $port, $timeout = 30, $options = []) {
		$this->host = $host;
		$this->port = $port;
		
		if (!preg_match('/^[a-z0-9]+:/', $this->host)) $this->host = 'tcp://'.$this->host;
		
		$context = stream_context_create($options);
		$this->resource = @stream_socket_client($this->host.':'.$this->port, $this->errorNumber, $this->errorDesc, $timeout, STREAM_CLIENT_CONNECT, $context);
		if ($this->resource === false) {
			throw new SystemException('Can not connect to ' . $host, 0, $this->errorDesc);
		}
		
		stream_set_timeout($this->resource, $timeout);
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
	
	/**
	 * Switches TLS support for this connection.
	 * Usually used in combination with 'STARTTLS'
	 * 
	 * @param	boolean	$enable		Whether TLS support should be enabled
	 * @return	boolean			True on success, false otherwise
	 */
	public function setTLS($enable) {
		if (!$this->hasTLSSupport()) return false;
		
		$cryptoType = STREAM_CRYPTO_METHOD_TLS_CLIENT;
		
		// PHP 5.6.8+ defines STREAM_CRYPTO_METHOD_TLS_CLIENT as STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT for BC reasons.
		// STREAM_CRYPTO_METHOD_TLS_ANY_CLIENT was introduced in PHP 5.6.8, but is not exposed to userland. Try to use
		// it for forward compatibility.
		if (defined('STREAM_CRYPTO_METHOD_TLS_ANY_CLIENT')) $cryptoType = STREAM_CRYPTO_METHOD_TLS_ANY_CLIENT;
		
		// Add bits for all known TLS versions for the reasons above.
		if (defined('STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT')) $cryptoType |= STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT;
		if (defined('STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT')) $cryptoType |= STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
		if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) $cryptoType |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
		
		return stream_socket_enable_crypto($this->resource, $enable, $cryptoType);
	}
	
	/**
	 * Returns whether TLS support is available.
	 * 
	 * @return	boolean
	 */
	public function hasTLSSupport() {
		return function_exists('stream_socket_enable_crypto');
	}
	
	/**
	 * Returns true if PHP supports SSL/TLS.
	 * 
	 * @return	boolean
	 */
	public static function supportsSSL() {
		if (static::$hasSSLSupport === null) {
			static::$hasSSLSupport = false;
			
			$transports = stream_get_transports();
			foreach ($transports as $transport) {
				if (preg_match('~^(ssl(v[23])?|tls(v[0-9\.]+)?)$~', $transport)) {
					static::$hasSSLSupport = true;
					break;
				}
			}
		}
		
		return static::$hasSSLSupport;
	}
	
	/**
	 * Disables SSL/TLS support on runtime regardless if PHP is theoretically capable of it.
	 */
	public static function disableSSL() {
		static::$hasSSLSupport = false;
	}
}
