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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Util
 */
final class HeaderUtil {
	/**
	 * gzip level to user
	 * @var	integer
	 */
	const GZIP_LEVEL = 1;
	
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
	 * 
	 * @param	string		$name
	 * @param	string		$value
	 * @param	integer		$expire
	 */
	public static function setCookie($name, $value = '', $expire = 0) {
		$application = ApplicationHandler::getInstance()->getActiveApplication();
		$addDomain = (mb_strpos($application->cookieDomain, '.') === false || StringUtil::endsWith($application->cookieDomain, '.lan') || StringUtil::endsWith($application->cookieDomain, '.local')) ? false : true;
		
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
		else if (!empty($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox') !== false) {
			// Firefox serves pages from cache, causing certain HTML elements to stay in an outdated state
			@header('Cache-Control: no-store');
		}
		
		if (HTTP_ENABLE_GZIP && !defined('HTTP_DISABLE_GZIP')) {
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
		
		// send X-Frame-Options
		if (HTTP_SEND_X_FRAME_OPTIONS) {
			@header('X-Frame-Options: SAMEORIGIN');
		}
		
		ob_start([self::class, 'parseOutput']);
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
		$javascript = [];
		self::$output = preg_replace_callback('~(?P<conditionBefore><!--\[IF [^<]+\s*)?<script data-relocate="true"(?P<script>.*?</script>\s*)(?P<conditionAfter><!\[ENDIF]-->\s*)?~s', function($matches) use (&$javascript) {
			$match = '';
			if (isset($matches['conditionBefore'])) $match .= $matches['conditionBefore'];
			$match .= '<script' . $matches['script'];
			if (isset($matches['conditionAfter'])) $match .= $matches['conditionAfter'];
			
			$javascript[] = $match;
			return '';
		}, self::$output);
		
		self::$output = str_replace('<!-- JAVASCRIPT_RELOCATE_POSITION -->', implode("\n", $javascript), self::$output);
		
		// 3rd party plugins may differ the actual output before it is sent to the browser
		// please be aware, that $eventObj is not available here due to this being a static
		// class. Use HeaderUtil::$output to modify it.
		if (!defined('NO_IMPORTS')) EventHandler::getInstance()->fireAction(self::class, 'parseOutput');
		
		// gzip compression
		if (self::$enableGzipCompression) {
			$size = strlen(self::$output);
			$crc = crc32(self::$output);
			
			$newOutput = "\x1f\x8b\x08\x00\x00\x00\x00\x00\x00\xff";
			$newOutput .= substr(gzcompress(self::$output, self::GZIP_LEVEL), 2, -4);
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
		WCF::getTPL()->assign([
			'url' => $location,
			'message' => $message,
			'wait' => $delay,
			'templateName' => 'redirect',
			'templateNameApplication' => 'wcf',
			'status' => $status
		]);
		WCF::getTPL()->display('redirect');
	}
	
	/**
	 * Forbid creation of HeaderUtil objects.
	 */
	private function __construct() {
		// does nothing
	}
}
