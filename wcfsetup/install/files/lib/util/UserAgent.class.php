<?php
namespace wcf\util;

/**
 * Represents the user agent.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Util
 * @since       5.4
 */
class UserAgent {
	/**
	 * @var string 
	 */
	protected $userAgent;
	
	/**
	 * @var ?string
	 */
	protected $browser;
	
	/**
	 * @var ?string
	 */
	protected $browserVersion;
	
	/**
	 * @var ?string
	 */
	protected $os;
	
	public function __construct(string $userAgent) {
		$this->userAgent = $userAgent;
		
		$this->detectOs();
		$this->detectBrowser();
	}
	
	/**
	 * Detects the browser on the basis of the user agent.
	 */
	protected function detectBrowser(): void {
		// lunascape
		if (preg_match('~lunascape[ /]([\d\.]+)~i', $this->userAgent, $match)) {
			$this->browser = 'Lunascape';
			$this->browserVersion = $match[1];
			return;
		}
		
		// sleipnir
		if (preg_match('~sleipnir/([\d\.]+)~i', $this->userAgent, $match)) {
			$this->browser = 'Sleipnir';
			$this->browserVersion = $match[1];
			return;
		}
		
		// uc browser
		if (preg_match('~(?:ucbrowser|uc browser|ucweb)[ /]?([\d\.]+)~i', $this->userAgent, $match)) {
			$this->browser = 'UC Browser';
			$this->browserVersion = $match[1];
			return;
		}
		
		// baidu browser
		if (preg_match('~(?:baidubrowser|flyflow)[ /]?([\d\.x]+)~i', $this->userAgent, $match)) {
			$this->browser = 'Baidubrowser';
			$this->browserVersion = $match[1];
			return;
		}
		
		// blackberry
		if (preg_match('~blackberry.*version/([\d\.]+)~i', $this->userAgent, $match)) {
			$this->browser = 'Blackberry';
			$this->browserVersion = $match[1];
			return;
		}
		
		// opera mobile
		if (preg_match('~opera/([\d\.]+).*(mobi|mini)~i', $this->userAgent, $match)) {
			$this->browser = 'Opera Mobile';
			$this->browserVersion = $match[1];
			return;
		}
		
		// opera
		if (preg_match('~opera.*version/([\d\.]+)|opr/([\d\.]+)~i', $this->userAgent, $match)) {
			$this->browser = 'Opera';
			$this->browserVersion = (isset($match[2]) ? $match[2] : $match[1]);
			return;
		}
		
		// thunderbird
		if (preg_match('~thunderbird/([\d\.]+)~i', $this->userAgent, $match)) {
			$this->browser = 'Thunderbird';
			$this->browserVersion = $match[1];
			return;
		}
		
		// icedragon
		if (preg_match('~icedragon/([\d\.]+)~i', $this->userAgent, $match)) {
			$this->browser = 'IceDragon';
			$this->browserVersion = $match[1];
			return;
		}
		
		// palemoon
		if (preg_match('~palemoon/([\d\.]+)~i', $this->userAgent, $match)) {
			$this->browser = 'PaleMoon';
			$this->browserVersion = $match[1];
			return;
		}
		
		// flock
		if (preg_match('~flock/([\d\.]+)~i', $this->userAgent, $match)) {
			$this->browser = 'Flock';
			$this->browserVersion = $match[1];
			return;
		}
		
		// iceweasel
		if (preg_match('~iceweasel/([\d\.]+)~i', $this->userAgent, $match)) {
			$this->browser = 'Iceweasel';
			$this->browserVersion = $match[1];
			return;
		}
		
		// firefox mobile
		if (preg_match('~(?:mobile.*firefox|fxios)/([\d\.]+)|fennec/([\d\.]+)~i', $this->userAgent, $match)) {
			$this->browser = 'Firefox Mobile';
			$this->browserVersion = (isset($match[2]) ? $match[2] : $match[1]);
			return;
		}
		
		// tapatalk 4
		if (preg_match('~tapatalk/([\d\.]+)?~i', $this->userAgent, $match)) {
			$this->browser = 'Tapatalk';
			$this->browserVersion = (isset($match[1]) ? $match[1] : 4);
			return;
		}
		
		// firefox
		if (preg_match('~firefox/([\d\.]+)~i', $this->userAgent, $match)) {
			$this->browser = 'Firefox';
			$this->browserVersion = $match[1];
			return;
		}
		
		// maxthon
		if (preg_match('~maxthon[ /]([\d\.]+)~i', $this->userAgent, $match)) {
			$this->browser = 'Maxthon';
			$this->browserVersion = $match[1];
			return;
		}
		
		// iemobile
		if (preg_match('~iemobile[ /]([\d\.]+)|MSIE ([\d\.]+).*XBLWP7~i', $this->userAgent, $match)) {
			$this->browser = 'Internet Explorer Mobile';
			$this->browserVersion = (isset($match[2]) ? $match[2] : $match[1]);
			return;
		}
		
		// ie
		if (preg_match('~msie ([\d\.]+)|Trident\/\d{1,2}.\d{1,2}; .*rv:([0-9]*)~i', $this->userAgent, $match)) {
			$this->browser = 'Internet Explorer';
			$this->browserVersion = (isset($match[2]) ? $match[2] : $match[1]);
			return;
		}
		
		// edge
		if (preg_match('~edge?/([\d\.]+)~i', $this->userAgent, $match)) {
			$this->browser = 'Microsoft Edge';
			$this->browserVersion = $match[1];
			return;
		}
		
		// edge mobile
		if (preg_match('~edga/([\d\.]+)~i', $this->userAgent, $match)) {
			$this->browser = 'Microsoft Edge Mobile';
			$this->browserVersion = $match[1];
			return;
		}
		
		// vivaldi
		if (preg_match('~vivaldi/([\d\.]+)~i', $this->userAgent, $match)) {
			$this->browser = 'Vivaldi';
			$this->browserVersion = $match[1];
			return;
		}
		
		// iron
		if (preg_match('~iron/([\d\.]+)~i', $this->userAgent, $match)) {
			$this->browser = 'Iron';
			$this->browserVersion = $match[1];
			return;
		}
		
		// coowon
		if (preg_match('~coowon/([\d\.]+)~i', $this->userAgent, $match)) {
			$this->browser = 'Coowon';
			$this->browserVersion = $match[1];
			return;
		}
		
		// coolnovo
		if (preg_match('~(?:coolnovo|chromeplus)/([\d\.]+)~i', $this->userAgent, $match)) {
			$this->browser = 'CoolNovo';
			$this->browserVersion = $match[1];
			return;
		}
		
		// yandex
		if (preg_match('~yabrowser/([\d\.]+)~i', $this->userAgent, $match)) {
			$this->browser = 'Yandex';
			$this->browserVersion = $match[1];
			return;
		}
		
		// midori
		if (preg_match('~midori/([\d\.]+)~i', $this->userAgent, $match)) {
			$this->browser = 'Midori';
			$this->browserVersion = $match[1];
			return;
		}
		
		// chrome mobile
		if (preg_match('~(?:crios|crmo)/([\d\.]+)|chrome/([\d\.]+).*mobile~i', $this->userAgent, $match)) {
			$this->browser = 'Chrome Mobile';
			$this->browserVersion = (isset($match[2]) ? $match[2] : $match[1]);
			return;
		}
		
		// kindle
		if (preg_match('~kindle/([\d\.]+)~i', $this->userAgent, $match)) {
			$this->browser = 'Kindle';
			$this->browserVersion = $match[1];
			return;
		}
		
		// silk
		if (preg_match('~silk/([\d\.]+)~i', $this->userAgent, $match)) {
			$this->browser = 'Silk';
			$this->browserVersion = $match[1];
			return;
		}
		
		// android browser
		if (preg_match('~Android ([\d\.]+).*AppleWebKit~i', $this->userAgent, $match)) {
			$this->browser = 'Android Browser';
			$this->browserVersion = $match[1];
			return;
		}
		
		// safari mobile
		if (preg_match('~([\d\.]+) Mobile/\w+ safari~i', $this->userAgent, $match)) {
			$this->browser = 'Safari Mobile';
			$this->browserVersion = $match[1];
			return;
		}
		
		// chrome
		if (preg_match('~(?:chromium|chrome)/([\d\.]+)~i', $this->userAgent, $match)) {
			$this->browser = 'Chrome';
			$this->browserVersion = $match[1];
			return;
		}
		
		// safari
		if (preg_match('~([\d\.]+) safari~i', $this->userAgent, $match)) {
			$this->browser = 'Safari';
			$this->browserVersion = $match[1];
			return;
		}
	}
	
	/**
	 * Detects the OS on the basis of the user agent.
	 */
	protected function detectOs(): void {
		// iOS 
		if (preg_match('/iphone/i', $this->userAgent)) {
			$this->os = "iOS";
			return;
		}
		
		// iOS 
		if (preg_match('/cfnetwork/i', $this->userAgent)) {
			$this->os = "iOS";
			return;
		}
		
		// Windows 
		if (preg_match('/windows/i', $this->userAgent)) {
			$this->os = "Windows";
			return;
		}
		
		// FreeBSD 
		if (preg_match('/freebsd/i', $this->userAgent)) {
			$this->os = "FreeBSD";
			return;
		}
		
		// netBSD 
		if (preg_match('/netbsd/i', $this->userAgent)) {
			$this->os = "NetBSD";
			return;
		}
		
		// openBSD 
		if (preg_match('/openbsd/i', $this->userAgent)) {
			$this->os = "OpenBSD";
			return;
		}
		
		// Android 
		if (preg_match('/android/i', $this->userAgent)) {
			$this->os = "Android";
			return;
		}
		
		// Linux 
		if (preg_match('/linux/i', $this->userAgent)) {
			$this->os = "Linux";
			return;
		}
		
		// iPad 
		if (preg_match('/ipad/i', $this->userAgent)) {
			$this->os = "iPad";
			return;
		}
		
		// webOS 
		if (preg_match('/web[0o]s/i', $this->userAgent)) {
			$this->os = "webOS";
			return;
		}
		
		// CrOS 
		if (preg_match('/cros/i', $this->userAgent)) {
			$this->os = "Chrome OS";
			return;
		}
		
		// macOS 
		if (preg_match('/mac/i', $this->userAgent)) {
			$this->os = "macOS";
			return;
		}
	}
	
	/**
	 * Returns the browser based on the user agent or null, if no browser can be determined.
	 */
	public function getBrowser(): ?string {
		return $this->browser;
	}
	
	/**
	 * Returns the browser version based on the user agent or null, if no browser version can be determined.
	 */
	public function getBrowserVersion(): ?string {
		return $this->browserVersion;
	}
	
	/**
	 * Returns the OS based on the user agent or null, if no OS can be determined.
	 */
	public function getOs(): ?string {
		return $this->os;
	}
	
	/**
	 * Checks if the User Agent gives an indicator about a tablet device.
	 * Heads up: This is only a basic test and can easily be falsified by the user.
	 */
	public function isTablet(): bool {
		if (preg_match('/tab(let)|ipad/i', $this->userAgent)) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Checks if the User Agent gives an indicator about a mobile device.
	 * Heads up: This is only a basic test and can easily be falsified by the user.
	 */
	public function isMobileBrowser(): bool {
		if (!$this->userAgent) return false;
		
		if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $this->userAgent)) {
			return true;
		}
		
		if (preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($this->userAgent, 0, 4))) {
			return true;
		}
		
		return false;
	}
}
