<?php
namespace wcf\util;
use wcf\system\WCF;

/**
 * Contains user-related functions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
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
		// minimum length is 3 characters, maximum length is 255 characters
		if (mb_strlen($name) < 3 || mb_strlen($name) > 255) {
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
			
			return mb_substr($userAgent, 0, 255);
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
		$userAgent = self::getUserAgent();
		if (!$userAgent) false;
		
		if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $userAgent)) {
			return true;
		}
		
		if (preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($userAgent, 0, 4))) {
			return true;
		}
		
		return false;
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
