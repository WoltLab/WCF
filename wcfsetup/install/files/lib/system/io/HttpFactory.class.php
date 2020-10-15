<?php
namespace wcf\system\io;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

/**
 * Factory for HTTP Clients.
 * 
 * As of right now the factory returns objects that implement Guzzle's ClientInterface.
 * Even if Guzzle will remain the HTTP client of choice for the foreseeable future you should
 * strive to use PSR-7 objects instead of relying on Guzzle's shortcuts for best compatibility.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Io
 * @since	5.3
 */
final class HttpFactory {
	/**
	 * @var Client
	 */
	private static $defaultClient;
	
	/**
	 * Returns a RFC 7231#5.5.3 compatible user agent.
	 * 
	 * @return string
	 */
	public static function getDefaultUserAgent() {
		return 'WoltLabSuite/'.\wcf\getMinorVersion();
	}
	
	/**
	 * Returns a reference to the default HTTP client.
	 * 
	 * @return ClientInterface
	 */
	public static function getDefaultClient() {
		if (self::$defaultClient === null) {
			self::$defaultClient = static::makeClient();
		}
		
		return self::$defaultClient;
	}
	
	/**
	 * Creates a new HTTP client.
	 * 
	 * The HTTP proxy will automatically be enabled, unless
	 * specifically removed by passing appropriate options.
	 * 
	 * @return ClientInterface
	 * @see Client
	 */
	public static function makeClient(array $options = []) {
		$defaults = [
			'proxy' => PROXY_SERVER_HTTP,
			'headers' => [],
		];
		
		foreach ($defaults as $key => $value) {
			if (!array_key_exists($key, $options)) {
				$options[$key] = $value;
			}
		}
		
		$foundUserAgent = false;
		foreach ($options['headers'] as $headerName => $value) {
			if (strtolower($headerName) === 'user-agent') {
				$foundUserAgent = true;
				break;
			}
		}
		if (!$foundUserAgent) {
			$options['headers']['user-agent'] = self::getDefaultUserAgent();
		}
		
		return new Client($options);
	}
}
