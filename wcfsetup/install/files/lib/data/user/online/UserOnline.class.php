<?php
namespace wcf\data\user\online;
use wcf\data\page\PageCache;
use wcf\data\spider\Spider;
use wcf\data\user\UserProfile;
use wcf\system\cache\builder\SpiderCacheBuilder;
use wcf\system\event\EventHandler;
use wcf\system\page\handler\IOnlineLocationPageHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;
use wcf\util\UserUtil;

/**
 * Represents a user who is online.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Online
 *
 * @property-read	integer|null	$pageID			id of the last visited page
 * @property-read	integer|null	$pageObjectID		id of the object the last visited page belongs to
 * @property-read	integer|null	$parentPageObjectID	id of the parent of the object the last visited page belongs to
 * @property-read	string|null	$userOnlineMarking	HTML code used to print the formatted name of a user group member
 */
class UserOnline extends UserProfile {
	/**
	 * location of the user
	 * @var	string
	 */
	protected $location = '';
	
	/**
	 * spider object
	 * @var	Spider
	 */
	protected $spider = null;
	
	/**
	 * Returns the formatted username.
	 * 
	 * @return	string
	 */
	public function getFormattedUsername() {
		$username = StringUtil::encodeHTML($this->username);
		
		if ($this->userOnlineMarking && $this->userOnlineMarking != '%s') {
			$username = str_replace('%s', $username, $this->userOnlineMarking);
		}
		
		if (
			$this->getPermission('user.profile.canHideOnlineStatus')
			&& $this->canViewOnlineStatus == UserProfile::ACCESS_NOBODY
		) {
			$username .= WCF::getLanguage()->get('wcf.user.usersOnline.invisible');
		}
		
		return $username;
	}
	
	/**
	 * Sets the location of the user. If no location is given, the method tries to
	 * automatically determine the location.
	 * 
	 * @param	string|null	$location
	 * @return	boolean		`true` if the location has been successfully set, otherwise `false`
	 */
	public function setLocation($location = null) {
		if ($location === null) {
			if ($this->pageID) {
				$page = PageCache::getInstance()->getPage($this->pageID);
				if ($page !== null) {
					if ($page->getHandler() !== null && $page->getHandler() instanceof IOnlineLocationPageHandler) {
						// refer to page handler
						/** @noinspection PhpUndefinedMethodInspection */
						$this->location = $page->getHandler()->getOnlineLocation($page, $this);
						return true;
					}
					else if ($page->isVisible() && $page->isAccessible()) {
						$title = $page->getTitle();
						if (!empty($title)) {
							if ($page->pageType != 'system') {
								$this->location = '<a href="' . StringUtil::encodeHTML($page->getLink()) . '">' . StringUtil::encodeHTML($title) . '</a>';
							}
							else {
								$this->location = StringUtil::encodeHTML($title);
							}
						}
						
						return ($this->location != '');
					}
				}
			}
			
			$this->location = '';
			return false;
		}
		
		$this->location = $location;
		return true;
	}
	
	/**
	 * Returns the location of the user.
	 * 
	 * @return	string
	 */
	public function getLocation() {
		return $this->location;
	}
	
	/**
	 * Returns the ip address.
	 * 
	 * @return	string
	 */
	public function getFormattedIPAddress() {
		if ($address = UserUtil::convertIPv6To4($this->ipAddress)) {
			return $address;
		}
		
		return $this->ipAddress;
	}
	
	/**
	 * Tries to retrieve browser name and version.
	 * 
	 * @return	string
	 */
	public function getBrowser() {
		$parameters = ['browser' => '', 'userAgent' => $this->userAgent];
		EventHandler::getInstance()->fireAction($this, 'getBrowser', $parameters);
		if (!empty($parameters['browser'])) {
			return $parameters['browser'];
		}
		
		// lunascape
		if (preg_match('~lunascape[ /]([\d\.]+)~i', $this->userAgent, $match)) {
			return 'Lunascape '.$match[1];
		}
		
		// sleipnir
		if (preg_match('~sleipnir/([\d\.]+)~i', $this->userAgent, $match)) {
			return 'Sleipnir '.$match[1];
		}
		
		// uc browser
		if (preg_match('~(?:ucbrowser|uc browser|ucweb)[ /]?([\d\.]+)~i', $this->userAgent, $match)) {
			return 'UC Browser '.$match[1];
		}
		
		// baidu browser
		if (preg_match('~(?:baidubrowser|flyflow)[ /]?([\d\.x]+)~i', $this->userAgent, $match)) {
			return 'Baidubrowser '.$match[1];
		}
		
		// blackberry
		if (preg_match('~blackberry.*version/([\d\.]+)~i', $this->userAgent, $match)) {
			return 'Blackberry '.$match[1];
		}
		
		// opera mobile
		if (preg_match('~opera/([\d\.]+).*(mobi|mini)~i', $this->userAgent, $match)) {
			return 'Opera Mobile '.$match[1];
		}
		
		// opera
		if (preg_match('~opera.*version/([\d\.]+)|opr/([\d\.]+)~i', $this->userAgent, $match)) {
			return 'Opera '.(isset($match[2]) ? $match[2] : $match[1]);
		}
		
		// thunderbird
		if (preg_match('~thunderbird/([\d\.]+)~i', $this->userAgent, $match)) {
			return 'Thunderbird '.$match[1];
		}
		
		// icedragon
		if (preg_match('~icedragon/([\d\.]+)~i', $this->userAgent, $match)) {
			return 'IceDragon '.$match[1];
		}
		
		// palemoon
		if (preg_match('~palemoon/([\d\.]+)~i', $this->userAgent, $match)) {
			return 'PaleMoon '.$match[1];
		}
		
		// flock
		if (preg_match('~flock/([\d\.]+)~i', $this->userAgent, $match)) {
			return 'Flock '.$match[1];
		}
		
		// iceweasel
		if (preg_match('~iceweasel/([\d\.]+)~i', $this->userAgent, $match)) {
			return 'Iceweasel '.$match[1];
		}
		
		// firefox mobile
		if (preg_match('~(?:mobile.*firefox|fxios)/([\d\.]+)|fennec/([\d\.]+)~i', $this->userAgent, $match)) {
			return 'Firefox Mobile '.(isset($match[2]) ? $match[2] : $match[1]);
		}
		
		// tapatalk 4
		if (preg_match('~tapatalk/([\d\.]+)?~i', $this->userAgent, $match)) {
			return 'Tapatalk '.(isset($match[1]) ? $match[1] : 4);
		}
		
		// firefox
		if (preg_match('~firefox/([\d\.]+)~i', $this->userAgent, $match)) {
			return 'Firefox '.$match[1];
		}
		
		// maxthon
		if (preg_match('~maxthon[ /]([\d\.]+)~i', $this->userAgent, $match)) {
			return 'Maxthon '.$match[1];
		}
		
		// iemobile
		if (preg_match('~iemobile[ /]([\d\.]+)|MSIE ([\d\.]+).*XBLWP7~i', $this->userAgent, $match)) {
			return 'Internet Explorer Mobile '.(isset($match[2]) ? $match[2] : $match[1]);
		}
		
		// ie
		if (preg_match('~msie ([\d\.]+)|Trident\/\d{1,2}.\d{1,2}; .*rv:([0-9]*)~i', $this->userAgent, $match)) {
			return 'Internet Explorer '.(isset($match[2]) ? $match[2] : $match[1]);
		}
		
		// edge
		if (preg_match('~edge?/([\d\.]+)~i', $this->userAgent, $match)) {
			return 'Microsoft Edge '.$match[1];
		}
		
		// edge mobile
		if (preg_match('~edga/([\d\.]+)~i', $this->userAgent, $match)) {
			return 'Microsoft Edge Mobile '.$match[1];
		}
		
		// vivaldi
		if (preg_match('~vivaldi/([\d\.]+)~i', $this->userAgent, $match)) {
			return 'Vivaldi '.$match[1];
		}
		
		// iron
		if (preg_match('~iron/([\d\.]+)~i', $this->userAgent, $match)) {
			return 'Iron '.$match[1];
		}
		
		// coowon
		if (preg_match('~coowon/([\d\.]+)~i', $this->userAgent, $match)) {
			return 'Coowon '.$match[1];
		}
		
		// coolnovo
		if (preg_match('~(?:coolnovo|chromeplus)/([\d\.]+)~i', $this->userAgent, $match)) {
			return 'CoolNovo '.$match[1];
		}
		
		// yandex
		if (preg_match('~yabrowser/([\d\.]+)~i', $this->userAgent, $match)) {
			return 'Yandex '.$match[1];
		}
		
		// midori
		if (preg_match('~midori/([\d\.]+)~i', $this->userAgent, $match)) {
			return 'Midori '.$match[1];
		}
		
		// chrome mobile
		if (preg_match('~(?:crios|crmo)/([\d\.]+)|chrome/([\d\.]+).*mobile~i', $this->userAgent, $match)) {
			return 'Chrome Mobile '.(isset($match[2]) ? $match[2] : $match[1]);
		}
		
		// kindle
		if (preg_match('~kindle/([\d\.]+)~i', $this->userAgent, $match)) {
			return 'Kindle '.$match[1];
		}
		
		// silk
		if (preg_match('~silk/([\d\.]+)~i', $this->userAgent, $match)) {
			return 'Silk '.$match[1];
		}
		
		// android browser
		if (preg_match('~Android ([\d\.]+).*AppleWebKit~i', $this->userAgent, $match)) {
			return 'Android Browser '.$match[1];
		}
		
		// safari mobile
		if (preg_match('~([\d\.]+) Mobile/\w+ safari~i', $this->userAgent, $match)) {
			return 'Safari Mobile '.$match[1];
		}
		
		// chrome
		if (preg_match('~(?:chromium|chrome)/([\d\.]+)~i', $this->userAgent, $match)) {
			return 'Chrome '.$match[1];
		}
		
		// safari
		if (preg_match('~([\d\.]+) safari~i', $this->userAgent, $match)) {
			return 'Safari '.$match[1];
		}
		
		return $this->userAgent;
	}
	
	/**
	 * Returns the spider object
	 * 
	 * @return	Spider
	 */
	public function getSpider() {
		if (!$this->spiderID) return null;
		
		if ($this->spider === null) {
			$spiderList = SpiderCacheBuilder::getInstance()->getData();
			$this->spider = $spiderList[$this->spiderID];
		}
		
		return $this->spider;
	}
}
