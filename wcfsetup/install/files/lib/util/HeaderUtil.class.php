<?php
namespace wcf\util;
use wcf\system\application\ApplicationHandler;
use wcf\system\request\RouteHandler;
use wcf\system\WCF;

/**
 * Contains header-related functions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	util
 * @category	Community Framework
 */
final class HeaderUtil {
	/**
	 * Alias to php setcookie() function.
	 */
	public static function setCookie($name, $value = '', $expire = 0) {
		$application = ApplicationHandler::getInstance()->getActiveApplication();
		$addDomain = (StringUtil::indexOf($application->cookieDomain, '.') === false || StringUtil::endsWith($application->cookieDomain, '.lan') || StringUtil::endsWith($application->cookieDomain, '.local')) ? false : true;
		@header('Set-Cookie: '.rawurlencode(COOKIE_PREFIX.$name).'='.rawurlencode($value).($expire ? '; expires='.gmdate('D, d-M-Y H:i:s', $expire).' GMT' : '').'; path='.$application->cookiePath.($addDomain ? '; domain='.$application->cookieDomain : '').(RouteHandler::secureConnection() ? '; secure' : '').'; HttpOnly', false);
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
		
		// enable gzip compression
		if (HTTP_ENABLE_GZIP && HTTP_GZIP_LEVEL > 0 && HTTP_GZIP_LEVEL < 10 && !defined('HTTP_DISABLE_GZIP')) {
			self::compressOutput();
		}
		
		// send Internet Explorer compatibility mode
		@header('X-UA-Compatible: IE=edge');
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
	 * Enables the gzip compression of the page output.
	 */
	public static function compressOutput() {
		if (function_exists('gzcompress') && !@ini_get('zlib.output_compression') && !@ini_get('output_handler') && isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
			if (strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip')) {
				@header('Content-Encoding: x-gzip');
			}
			else {
				@header('Content-Encoding: gzip');
			}
			ob_start(array('wcf\util\HeaderUtil', 'getCompressedOutput'));
		}
	}
	
	/**
	 * Outputs the compressed page content.
	 */
	public static function getCompressedOutput($output) {
		$size = strlen($output);
		$crc = crc32($output);
		
		$newOutput = "\x1f\x8b\x08\x00\x00\x00\x00\x00\x00\xff";
		$newOutput .= substr(gzcompress($output, HTTP_GZIP_LEVEL), 2, -4);
		unset($output);
		$newOutput .= pack('V', $crc);
		$newOutput .= pack('V', $size);
		
		return $newOutput;
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
	 */
	public static function delayedRedirect($location, $message, $delay = 5) {
		WCF::getTPL()->assign(array(
			'url' => $location,
			'message' => $message,
			'wait' => $delay,
			'templateName' => 'redirect'
		));
		WCF::getTPL()->display('redirect');
	}
	
	private function __construct() { }
}
