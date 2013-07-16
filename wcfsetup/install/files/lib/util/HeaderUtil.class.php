<?php
namespace wcf\util;
use wcf\system\application\ApplicationHandler;
use wcf\system\event\EventHandler;
use wcf\system\request\RouteHandler;
use wcf\system\WCF;

/**
 * Contains header-related functions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	util
 * @category	Community Framework
 */
final class HeaderUtil {
	/**
	 * gzip compression
	 * @var	boolean
	 */
	protected static $enableGzipCompression = false;
	
	/**
	 * output HTML
	 * @var	string
	 */
	public static $output = '';
	
	/**
	 * Alias to php setcookie() function.
	 */
	public static function setCookie($name, $value = '', $expire = 0) {
		$application = ApplicationHandler::getInstance()->getActiveApplication();
		$addDomain = (StringUtil::indexOf($application->cookieDomain, '.') === false || StringUtil::endsWith($application->cookieDomain, '.lan') || StringUtil::endsWith($application->cookieDomain, '.local')) ? false : true;
		
		@header('Set-Cookie: '.rawurlencode(COOKIE_PREFIX.$name).'='.rawurlencode($value).($expire ? '; expires='.gmdate('D, d-M-Y H:i:s', $expire).' GMT; max-age='.($expire - TIME_NOW) : '').'; path='.$application->cookiePath.($addDomain ? '; domain='.$application->cookieDomain : '').(RouteHandler::secureConnection() ? '; secure' : '').'; HttpOnly', false);
	}
	
	/**
	 * Sends the headers of a page.
	 */
	public static function sendHeaders() {
		// send content type
		@header('Content-Type: text/html; charset=UTF-8');
		
		// send no cache headers
		if (HTTP_ENABLE_NO_CACHE_HEADERS && !WCF::getSession()->spiderID) {
			self::sendNoCacheHeaders();
		}
		
		if (HTTP_ENABLE_GZIP && HTTP_GZIP_LEVEL > 0 && HTTP_GZIP_LEVEL < 10 && !defined('HTTP_DISABLE_GZIP')) {
			if (function_exists('gzcompress') && !@ini_get('zlib.output_compression') && !@ini_get('output_handler') && isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
				self::$enableGzipCompression = true;
				
				if (strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip')) {
					@header('Content-Encoding: x-gzip');
				}
				else {
					@header('Content-Encoding: gzip');
				}
			}
		}
		
		// send Internet Explorer compatibility mode
		@header('X-UA-Compatible: IE=edge');
		
		ob_start(array('wcf\util\HeaderUtil', 'parseOutput'));
	}
	
	/**
	 * Sends no cache headers.
	 */
	public static function sendNoCacheHeaders() {
		@header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); 
		@header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
		@header('Cache-Control: no-cache, must-revalidate');
		@header('Pragma: no-cache');
	}
	
	/**
	 * Parses the rendered output.
	 * 
	 * @param	string		$output
	 * @return	string
	 */
	public static function parseOutput($output) {
		self::$output = $output;
		
		// move script tags to the bottom of the page
		$javascript = array();
		self::$output = preg_replace_callback('~<script(.*?)</script>~s', function($matches) use (&$javascript) {
			$javascript[] = $matches[0];
			return '';
		}, self::$output);
		
		self::$output = str_replace(array('</body>', '</html>'), array('', ''), self::$output);
		self::$output .= "\n".implode("\n", $javascript)."\n</body></html>";
		
		// 3rd party plugins may differ the actual output before it is sent to the browser
		// please be aware, that $eventObj is not available here due to this being a static
		// class. Use HeaderUtil::$output to modify it.
		if (!defined('NO_IMPORTS')) EventHandler::getInstance()->fireAction('wcf\util\HeaderUtil', 'parseOutput');
		
		// gzip compression
		if (self::$enableGzipCompression) {
			$size = strlen(self::$output);
			$crc = crc32(self::$output);
			
			$newOutput = "\x1f\x8b\x08\x00\x00\x00\x00\x00\x00\xff";
			$newOutput .= substr(gzcompress(self::$output, HTTP_GZIP_LEVEL), 2, -4);
			$newOutput .= pack('V', $crc);
			$newOutput .= pack('V', $size);
			
			self::$output = $newOutput;
		}
		
		return self::$output;
	}
	
	/**
	 * Redirects the user agent.
	 * 
	 * @param	string		$location
	 * @param	boolean		$sendStatusCode
	 */
	public static function redirect($location, $sendStatusCode = false) {
		//if ($sendStatusCode) @header('HTTP/1.0 301 Moved Permanently');
		if ($sendStatusCode) @header('HTTP/1.1 307 Temporary Redirect');
		header('Location: '.$location);
	}
	
	/**
	 * Does a delayed redirect.
	 * 
	 * @param	string		$location
	 * @param	string		$message
	 * @param	integer		$delay
	 * @param	string		$status
	 */
	public static function delayedRedirect($location, $message, $delay = 5, $status = 'success') {
		WCF::getTPL()->assign(array(
			'url' => $location,
			'message' => $message,
			'wait' => $delay,
			'templateName' => 'redirect',
			'status' => $status
		));
		WCF::getTPL()->display('redirect');
	}
	
	private function __construct() { }
}
