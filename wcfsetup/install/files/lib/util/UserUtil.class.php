<?php
namespace wcf\util;
use wcf\system\event\EventHandler;
use wcf\system\WCF;

/**
 * Contains user-related functions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Util
 */
final class UserUtil {
	/**
	 * Returns true if the given name is a valid username.
	 * 
	 * @param	string		$name
	 * @return	boolean
	 */
	public static function isValidUsername($name) {
		// minimum length is 3 characters, maximum length is 100 characters
		if (mb_strlen($name) < 3 || mb_strlen($name) > 100) {
			return false;
		}
		
		// check illegal characters
		if (!preg_match('!^[^,\n]+$!', $name)) {
			return false;
		}
		// check long words
		$words = preg_split('!\s+!', $name, -1, PREG_SPLIT_NO_EMPTY);
		foreach ($words as $word) {
			if (mb_strlen($word) > 20) {
				return false;
			}
		}
		// username must not be a valid e-mail
		if (self::isValidEmail($name)) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Returns true if the given username is available.
	 * 
	 * @param	string		$name
	 * @return	boolean
	 */
	public static function isAvailableUsername($name) {
		$sql = "SELECT	COUNT(username)
			FROM	wcf".WCF_N."_user
			WHERE	username = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$name]);
		
		return $statement->fetchSingleColumn() == 0;
	}
	
	/**
	 * Returns true if the given e-mail is a valid address.
	 * @see	http://www.faqs.org/rfcs/rfc821.html
	 * 
	 * @param	string		$email
	 * @return	boolean
	 */
	public static function isValidEmail($email) {
		if (mb_strlen($email) > 191) {
			return false;
		}
		
		// local-part
		$c = '!#\$%&\'\*\+\-\/0-9=\?a-z\^_`\{\}\|~';
		$string = '['.$c.']*(?:\\\\[\x00-\x7F]['.$c.']*)*';
		$localPart = $string.'(?:\.'.$string.')*';
		
		// domain
		$name = '[a-z0-9](?:[a-z0-9-]*[a-z0-9])?';
		$domain = $name.'(?:\.'.$name.')*\.[a-z]{2,}';
		
		// mailbox
		$mailbox = $localPart.'@'.$domain;
		
		return preg_match('/^'.$mailbox.'$/i', $email);
	}
	
	/**
	 * Returns true if the given email address is available.
	 * 
	 * @param	string		$email
	 * @return	boolean
	 */
	public static function isAvailableEmail($email) {
		$sql = "SELECT	COUNT(email)
			FROM	wcf".WCF_N."_user
			WHERE	email = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$email]);
		
		return $statement->fetchSingleColumn() == 0;
	}
	
	/**
	 * Returns the user agent of the client.
	 * 
	 * @return	string
	 */
	public static function getUserAgent() {
		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			$userAgent = $_SERVER['HTTP_USER_AGENT'];
			if (!StringUtil::isUTF8($userAgent)) {
				$userAgent = StringUtil::convertEncoding('ISO-8859-1', 'UTF-8', $userAgent);
			}
			
			return mb_substr($userAgent, 0, 191);
		}
		return '';
	}
	
	/**
	 * Returns true if the active user uses a mobile browser.
	 * @see	http://detectmobilebrowser.com
	 * 
	 * @return	boolean
	 */
	public static function usesMobileBrowser() {
		return (new UserAgent(self::getUserAgent()))->isMobileBrowser();
	}
	
	/**
	 * Returns the ipv6 address of the client.
	 * 
	 * @return	string
	 */
	public static function getIpAddress() {
		$REMOTE_ADDR = '';
		if (isset($_SERVER['REMOTE_ADDR'])) $REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
		
		// darwin fix
		if ($REMOTE_ADDR == '::1' || $REMOTE_ADDR == 'fe80::1') {
			$REMOTE_ADDR = '127.0.0.1';
		}
		
		$REMOTE_ADDR = self::convertIPv4To6($REMOTE_ADDR);
		
		return $REMOTE_ADDR;
	}
	
	/**
	 * Converts given ipv4 to ipv6.
	 * 
	 * @param	string		$ip
	 * @return	string
	 */
	public static function convertIPv4To6($ip) {
		// drop Window's scope id (confused PHP)
		$ip = preg_replace('~%[^%]+$~', '', $ip);
		
		if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
			// given ip is already ipv6
			return $ip;
		}
		
		if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
			// invalid ip given
			return '';
		}
		
		$ipArray = array_pad(explode('.', $ip), 4, 0);
		$part7 = base_convert(($ipArray[0] * 256) + $ipArray[1], 10, 16);
		$part8 = base_convert(($ipArray[2] * 256) + $ipArray[3], 10, 16);
		
		return '::ffff:'.$part7.':'.$part8;
	}
	
	/**
	 * Converts IPv6 embedded IPv4 address into IPv4 or returns input if true IPv6.
	 * 
	 * @param	string		$ip
	 * @return	string
	 */
	public static function convertIPv6To4($ip) {
		// validate if given IP is a proper IPv6 address
		if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
			// validate if given IP is a proper IPv4 address
			if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
				// ip address is invalid
				return '';
			}
			
			return $ip;
		}
		
		// check if ip is a masked IPv4 address
		if (substr($ip, 0, 7) == '::ffff:') {
			$ip = substr($ip, 7);
			if (preg_match('~^([a-f0-9]{1,4}):([a-f0-9]{1,4})$~', $ip, $matches)) {
				$ip = [
					base_convert($matches[1], 16, 10),
					base_convert($matches[2], 16, 10)
				];
				
				$ipParts = [];
				$tmp = $ip[0] % 256;
				$ipParts[] = ($ip[0] - $tmp) / 256;
				$ipParts[] = $tmp;
				$tmp = $ip[1] % 256;
				$ipParts[] = ($ip[1] - $tmp) / 256;
				$ipParts[] = $tmp;
				
				return implode('.', $ipParts);
			}
			else {
				return $ip;
			}
		}
		else {
			// given ip is an IPv6 address and cannot be converted
			return $ip;
		}
	}
	
	/**
	 * Returns the request uri of the active request.
	 * 
	 * @return	string
	 */
	public static function getRequestURI() {
		$REQUEST_URI = '';
		
		$appendQueryString = true;
		if (!empty($_SERVER['ORIG_PATH_INFO']) && strpos($_SERVER['ORIG_PATH_INFO'], '.php') !== false) {
			$REQUEST_URI = $_SERVER['ORIG_PATH_INFO'];
		}
		else if (!empty($_SERVER['ORIG_SCRIPT_NAME'])) {
			$REQUEST_URI = $_SERVER['ORIG_SCRIPT_NAME'];
		}
		else if (!empty($_SERVER['SCRIPT_NAME']) && (isset($_SERVER['PATH_INFO']) && !empty($_SERVER['PATH_INFO']))) {
			$REQUEST_URI = $_SERVER['SCRIPT_NAME'] . $_SERVER['PATH_INFO'];
		}
		else if (isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI'])) {
			$REQUEST_URI = $_SERVER['REQUEST_URI'];
			$appendQueryString = false;
		}
		else if (!empty($_SERVER['PHP_SELF'])) {
			$REQUEST_URI = $_SERVER['PHP_SELF'];
		}
		else if (!empty($_SERVER['PATH_INFO'])) {
			$REQUEST_URI = $_SERVER['PATH_INFO'];
		}
		if ($appendQueryString && !empty($_SERVER['QUERY_STRING'])) {
			$REQUEST_URI .= '?'.$_SERVER['QUERY_STRING'];
		}
		
		// fix encoding
		if (!StringUtil::isUTF8($REQUEST_URI)) {
			$REQUEST_URI = StringUtil::convertEncoding('ISO-8859-1', 'UTF-8', $REQUEST_URI);
		}
		
		return mb_substr(FileUtil::unifyDirSeparator($REQUEST_URI), 0, 255);
	}
	
	/**
	 * Forbid creation of UserUtil objects.
	 */
	private function __construct() {
		// does nothing
	}
}
